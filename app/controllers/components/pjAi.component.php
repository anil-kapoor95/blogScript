<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
/**
 * pjAi
 *
 * Shared helper for talking to the configured AI provider (OpenAI, Anthropic,
 * Google). Used by:
 *  - pjAdminOptions::pjActionVerifyAiKey()   -> pjAi::verify()        (AI Settings tab)
 *  - pjAdminPosts::pjActionGenerateAi()      -> pjAi::generatePost()  (AI Writer)
 *  - pjAdminPosts::pjActionGenerateAiImage() -> pjAi::generateImage() (AI Writer - optional featured image)
 *
 * All HTTP calls happen server-side via cURL so provider API keys never reach
 * the browser.
 */
class pjAi
{
	/**
	 * Fixed output size every AI-generated featured image is cropped
	 * and resized to (center-crop to this aspect ratio, then scaled),
	 * regardless of what size/ratio the provider itself returned.
	 */
	const FEATURED_IMAGE_WIDTH  = 700;
	const FEATURED_IMAGE_HEIGHT = 500;

	/**
	 * Ping the provider's "list models" endpoint to confirm a key works.
	 * Returns array('valid' => bool, 'text' => string).
	 */
	static public function verify($provider, $apiKey)
	{
		if (!function_exists('curl_init'))
		{
			return array('valid' => false, 'text' => __('aiVerifyNoCurl', true));
		}

		switch ($provider)
		{
			case 'openai':
				return self::_verifyGet(
					'https://api.openai.com/v1/models',
					array('Authorization: Bearer ' . $apiKey)
				);
			case 'anthropic':
				return self::_verifyGet(
					'https://api.anthropic.com/v1/models',
					array('x-api-key: ' . $apiKey, 'anthropic-version: 2023-06-01')
				);
			case 'google':
				return self::_verifyGet(
					'https://generativelanguage.googleapis.com/v1beta/models?key=' . urlencode($apiKey),
					array()
				);
		}

		return array('valid' => false, 'text' => __('aiVerifyNoProvider', true));
	}

	/**
	 * Generate a draft blog post (title + HTML body) for the given topic,
	 * using the provider/model/key already saved in AI Settings.
	 * Returns array('ok' => bool, 'title' => string, 'body' => string, 'text' => string on failure).
	 */
	static public function generatePost($provider, $model, $apiKey, $topic, $language = '')
	{
		if (!function_exists('curl_init'))
		{
			return array('ok' => false, 'text' => __('aiVerifyNoCurl', true));
		}
		if (trim((string) $apiKey) === '')
		{
			return array('ok' => false, 'text' => __('aiMsgNoKey', true));
		}
		if (trim((string) $topic) === '')
		{
			return array('ok' => false, 'text' => __('aiMsgEnterTopic', true));
		}

		$system = "You are a professional blog writer. Given a topic, write an engaging, well-structured blog post. "
				. "Respond with exactly two parts, in this exact format and nothing else (no markdown code fences):\n"
				. "TITLE: <a concise, compelling post title, no surrounding quotes>\n"
				. "---\n"
				. "<the post body as clean HTML using only <p>, <h2>, <h3>, <ul>, <ol>, <li>, <strong>, <em> tags; "
				. "no <html>, <head>, <body>, <script>, inline styles, or the title repeated inside the body>"
				. self::_languageRule($language);
		$user = "Topic: " . $topic;

		switch ($provider)
		{
			case 'openai':
				$raw = self::_openaiChat($model, $system, $user, $apiKey);
				break;
			case 'anthropic':
				$raw = self::_anthropicChat($model, $system, $user, $apiKey);
				break;
			case 'google':
				$raw = self::_googleChat($model, $system, $user, $apiKey);
				break;
			default:
				return array('ok' => false, 'text' => __('aiVerifyNoProvider', true));
		}

		if ($raw['ok'] !== true)
		{
			return $raw;
		}

		return self::_parseGenerated($raw['text']);
	}

	/**
	 * Instruction that makes the model write the whole draft (title, body and the SEO fields) in the
	 * language the post is being written in - the topic itself may be typed in any language. The
	 * block labels (TITLE:, SEO_TITLE: ...) stay in English because they are parsed afterwards.
	 */
	static private function _languageRule($language)
	{
		$language = trim((string) $language);
		if ($language === '')
		{
			return '';
		}
		return "\n\nLANGUAGE: write the title, the body and the SEO fields entirely in " . $language
			. ", whatever language the topic is written in. Keep the labels (TITLE:, SEO_TITLE:, SEO_DESCRIPTION:, BODY:, ===OPTION_<n>===) and the HTML tag names exactly as specified above.";
	}

	static private function _parseGenerated($text)
	{
		$text = trim((string) $text);
		// Strip a leading/trailing markdown code fence some models add anyway.
		$text = preg_replace('/^```[a-zA-Z]*\s*|\s*```\s*$/', '', $text);

		if (preg_match('/^TITLE:\s*(.+?)\s*\R+-{3,}\s*\R+(.*)$/is', $text, $m))
		{
			$title = trim($m[1]);
			$body  = trim($m[2]);
			if ($title !== '' && $body !== '')
			{
				return array('ok' => true, 'title' => $title, 'body' => $body);
			}
		}

		// Model didn't follow the requested format - fall back to using the
		// full response as the body rather than failing outright.
		return array('ok' => true, 'title' => '', 'body' => $text);
	}

	/**
	 * Generate $count distinct draft options (title, body, SEO title, SEO
	 * description) for the given topic in a single provider call, optionally
	 * steered by a tone (professional|casual|friendly) and length
	 * (short|medium|long). Used by the "AI Writer" wizard page, which lets
	 * the user pick one of several drafts instead of a single generation.
	 * Returns array('ok' => bool, 'options' => array[], 'text' => string on failure).
	 * Each option is array('title', 'body', 'meta_title', 'meta_description').
	 */
	static public function generatePostOptions($provider, $model, $apiKey, $topic, $tone = '', $length = '', $count = 3, $language = '')
	{
		if (!function_exists('curl_init'))
		{
			return array('ok' => false, 'text' => __('aiVerifyNoCurl', true));
		}
		if (trim((string) $apiKey) === '')
		{
			return array('ok' => false, 'text' => __('aiMsgNoKey', true));
		}
		if (trim((string) $topic) === '')
		{
			return array('ok' => false, 'text' => __('aiMsgEnterTopic', true));
		}

		$toneMap = array(
			'professional' => 'a professional, authoritative tone',
			'casual'       => 'a casual, conversational tone',
			'friendly'     => 'a warm, friendly tone',
		);
		/**
		 * Character targets per length tier for the generated post BODY. These
		 * are a hard cap, not just a hint to the model - _truncateHtml() below
		 * enforces it after generation, since models don't reliably stick to a
		 * requested length on their own.
		 *
		 * Keep these numbers in sync with LENGTH_CHAR_MAP in pjAdminPosts.js
		 * (used there to show the "approx. N characters" hint next to the
		 * Length dropdown, and the actual count on each generated option) -
		 * there's no shared config between the PHP and JS side, so both need
		 * updating together.
		 */
		$lengthCharMap = array(
			'short'  => 800,
			'medium' => 1500,
			'long'   => 2500,
		);
		$lengthMap = array(
			'short'  => "a short length (aim for about {$lengthCharMap['short']} characters in the body)",
			'medium' => "a medium length (aim for about {$lengthCharMap['medium']} characters in the body)",
			'long'   => "a long length (aim for about {$lengthCharMap['long']} characters in the body)",
		);
		$toneKey   = strtolower(trim((string) $tone));
		$lengthKey = strtolower(trim((string) $length));
		if (!isset($lengthCharMap[$lengthKey]))
		{
			$lengthKey = 'medium';
		}
		$toneInstruction   = isset($toneMap[$toneKey]) ? $toneMap[$toneKey] : 'a clear, engaging tone';
		$lengthInstruction = $lengthMap[$lengthKey];
		$targetChars       = $lengthCharMap[$lengthKey];

		$count = (int) $count > 0 ? (int) $count : 3;

		$system = "You are a professional blog writer. Given a topic, write exactly {$count} DIFFERENT draft blog posts on that "
				. "topic - each with a distinct angle or headline, not just reworded copies of each other. Write every draft in "
				. "{$toneInstruction} and {$lengthInstruction}. "
				. "Respond with exactly {$count} blocks and nothing else (no markdown code fences, no extra commentary before, "
				. "between, or after the blocks). Each block must use this exact format:\n"
				. "===OPTION_<n>===\n"
				. "TITLE: <a concise, compelling post title, no surrounding quotes>\n"
				. "SEO_TITLE: <an SEO-friendly title, under 60 characters, no surrounding quotes>\n"
				. "SEO_DESCRIPTION: <an SEO meta description, under 160 characters, no surrounding quotes>\n"
				. "BODY:\n"
				. "<the post body as clean HTML using only <p>, <h2>, <h3>, <ul>, <ol>, <li>, <strong>, <em> tags; "
				. "no <html>, <head>, <body>, <script>, inline styles, or the title repeated inside the body>\n\n"
				. "Replace <n> with 1, 2, 3, ... {$count} in order, one block per option."
				. self::_languageRule($language);
		$user = "Topic: " . $topic;

		// Generating multiple full drafts needs more headroom than a single
		// one. Scale the request to the chosen length tier (roughly 3
		// characters per token for English, plus a buffer per option for the
		// title/SEO fields and formatting) instead of a fixed budget, so a
		// "short" request doesn't pay for tokens only a "long" one needs.
		// A character costs about 1 token in a non-Latin script (Cyrillic, Greek,
		// Arabic, CJK ...) against about 1/3 token in Latin text, so the budget
		// is sized by the script of the language the draft is written in -
		// otherwise the reply is cut off mid-draft (a body of one character and
		// missing options).
		$charsPerToken = (trim((string) $language) !== '' && preg_match('/[^\x{0000}-\x{024F}\x{1E00}-\x{1EFF}]/u', $language)) ? 1 : 3;
		$maxTokens = (int) ((($targetChars / $charsPerToken) + 400) * $count);
		$maxTokens = max(600, min(16000, $maxTokens));

		switch ($provider)
		{
			case 'openai':
				$raw = self::_openaiChat($model, $system, $user, $apiKey, $maxTokens);
				break;
			case 'anthropic':
				$raw = self::_anthropicChat($model, $system, $user, $apiKey, $maxTokens);
				break;
			case 'google':
				$raw = self::_googleChat($model, $system, $user, $apiKey, $maxTokens);
				break;
			default:
				return array('ok' => false, 'text' => __('aiVerifyNoProvider', true));
		}

		if ($raw['ok'] !== true)
		{
			return $raw;
		}

		$result = self::_parseGeneratedOptions($raw['text'], $count);

		if ($result['ok'] === true && !empty($result['options']))
		{
			foreach ($result['options'] as &$opt)
			{
				if (isset($opt['body']) && $opt['body'] !== '')
				{
					$opt['body'] = self::_truncateHtml($opt['body'], $targetChars);
				}
			}
			unset($opt);
		}

		return $result;
	}

	/**
	 * Hard-caps an HTML fragment to at most $maxChars of visible text,
	 * without cutting a tag in half and without leaving any tag unclosed.
	 * Only counts characters outside of tags against the limit; markup itself
	 * is free. Used to enforce the Length tier's character target on a
	 * generated post body, since models don't reliably stop on their own.
	 */
	/**
	 * Generate a single featured/header image for a post from a prompt
	 * (built by the caller from the post topic), using the provider/key
	 * already saved in AI Settings. Unlike generatePost()/generatePostOptions(),
	 * this ignores the configured chat model (o_ai_model) - image generation
	 * always uses a fixed, provider-specific image model, since a chat model
	 * name isn't meaningful here.
	 *
	 * Returns array('ok' => bool, 'image' => array('mime' => string, 'base64' => string), 'text' => string on failure).
	 * The image is returned inline as base64 rather than saved to disk - the
	 * caller (the AI Writer wizard) only ever needs to preview it and, if the
	 * author picks that draft, hand it to the normal post image upload flow
	 * client-side, so there's nothing server-side to clean up if it's never used.
	 */
	static public function generateImage($provider, $apiKey, $prompt)
	{
		if (!function_exists('curl_init'))
		{
			return array('ok' => false, 'text' => __('aiVerifyNoCurl', true));
		}
		if (trim((string) $apiKey) === '')
		{
			return array('ok' => false, 'text' => __('aiMsgNoKey', true));
		}
		if (trim((string) $prompt) === '')
		{
			return array('ok' => false, 'text' => __('aiMsgEnterTopic', true));
		}

		switch ($provider)
		{
			case 'openai':
				$result = self::_openaiImage($prompt, $apiKey);
				break;
			case 'google':
				$result = self::_googleImage($prompt, $apiKey);
				break;
			case 'anthropic':
				// Anthropic/Claude has no image generation API - only the chat
				// models used by generatePost()/generatePostOptions() above.
				return array('ok' => false, 'text' => __('aiMsgImageNoProvider', true));
			default:
				return array('ok' => false, 'text' => __('aiVerifyNoProvider', true));
		}

		// Every provider returns its image at whatever size/aspect ratio it
		// feels like (OpenAI: fixed 1792x1024; Google: not controllable at
		// all) - normalize to the blog's featured-image canvas here, once, so
		// neither _openaiImage() nor _googleImage() (nor a future provider)
		// has to deal with it individually.
		if ($result['ok'] === true && isset($result['image']))
		{
			$result['image'] = self::_resizeImageCover($result['image'], self::FEATURED_IMAGE_WIDTH, self::FEATURED_IMAGE_HEIGHT);
		}

		return $result;
	}

	/**
	 * OpenAI Images API (DALL-E 3). Requests a landscape image (closer to the
	 * blog post thumb/medium crop ratios than a square one) back as base64 so
	 * it never needs a second authenticated request to fetch it from a URL.
	 */
	static private function _openaiImage($prompt, $apiKey)
	{
		$payload = json_encode(array(
			'model'           => 'dall-e-3',
			'prompt'          => $prompt,
			'n'               => 1,
			'size'            => '1792x1024',
			'response_format' => 'b64_json',
		));

		$resp = self::_post('https://api.openai.com/v1/images/generations', $payload, array(
			'Content-Type: application/json',
			'Authorization: Bearer ' . $apiKey,
		));

		if ($resp['ok'] !== true)
		{
			return $resp;
		}

		$json = json_decode($resp['body'], true);
		$b64  = isset($json['data'][0]['b64_json']) ? $json['data'][0]['b64_json'] : '';

		if ($b64 === '')
		{
			return array('ok' => false, 'text' => __('aiMsgGenerateFailed', true));
		}

		return array('ok' => true, 'image' => array('mime' => 'image/png', 'base64' => $b64));
	}

	/**
	 * Google image generation via the Generative Language API's regular
	 * :generateContent endpoint (the same one _googleChat() below uses for
	 * text), asking for an IMAGE response part.
	 *
	 * This used to call the standalone Imagen model via a separate :predict
	 * endpoint (imagen-3.0-generate-002), but Google has since folded image
	 * generation into its mainline Gemini chat models and retired the old
	 * Imagen model ids outright - a call to the old endpoint/model now comes
	 * back HTTP 404 ("not found"), which is a different failure than an
	 * invalid/rate-limited key. Google has renamed the image-capable model
	 * more than once already, so this tries a short list of known-current
	 * ids in order and only moves on to the next one if the current id
	 * specifically doesn't exist for this account/API version - any other
	 * kind of failure (bad key, quota, connection) would fail identically
	 * for all of them, so that's surfaced immediately instead.
	 */
	static private function _googleImage($prompt, $apiKey)
	{
		$candidates = array('gemini-2.5-flash-image', 'gemini-3.1-flash-image');

		$payload = json_encode(array(
			'contents' => array(
				array('role' => 'user', 'parts' => array(array('text' => $prompt))),
			),
			'generationConfig' => array('responseModalities' => array('TEXT', 'IMAGE')),
		));

		$lastResp = array('ok' => false, 'text' => __('aiMsgGenerateFailed', true));

		foreach ($candidates as $mdl)
		{
			$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($mdl)
				 . ':generateContent?key=' . urlencode($apiKey);

			$resp = self::_post($url, $payload, array('Content-Type: application/json'));
			$lastResp = $resp;

			if ($resp['ok'] !== true)
			{
				if (strpos($resp['text'], '(HTTP 404)') === false)
				{
					return $resp;
				}
				continue;
			}

			$json  = json_decode($resp['body'], true);
			$parts = isset($json['candidates'][0]['content']['parts']) && is_array($json['candidates'][0]['content']['parts'])
				   ? $json['candidates'][0]['content']['parts']
				   : array();

			foreach ($parts as $part)
			{
				if (isset($part['inlineData']['data']) && $part['inlineData']['data'] !== '')
				{
					$mime = isset($part['inlineData']['mimeType']) && $part['inlineData']['mimeType'] !== ''
						  ? $part['inlineData']['mimeType']
						  : 'image/png';
					return array('ok' => true, 'image' => array('mime' => $mime, 'base64' => $part['inlineData']['data']));
				}
			}

			return array('ok' => false, 'text' => __('aiMsgGenerateFailed', true));
		}

		return $lastResp;
	}

	/**
	 * Center-crops then scales a base64 image down/up to exactly
	 * $targetW x $targetH, so every provider's output lands on the same
	 * canvas size regardless of what it natively generated. Crops (never
	 * stretches) to the target aspect ratio first - a straight resize would
	 * squash a square or portrait image; this instead keeps the crop
	 * centered and discards the overhanging edges, the same "cover" behavior
	 * as CSS background-size: cover.
	 *
	 * Requires the GD extension. If it's missing, or the image data can't be
	 * decoded, the original image is returned unresized rather than failing
	 * the whole request - a wrong-sized featured image is recoverable
	 * (crop/replace it later in the editor), a generation that errors out
	 * isn't.
	 */
	static private function _resizeImageCover($image, $targetW, $targetH)
	{
		if (!extension_loaded('gd') || !function_exists('imagecreatefromstring'))
		{
			return $image;
		}

		$raw = base64_decode($image['base64'], true);
		if ($raw === false || $raw === '')
		{
			return $image;
		}

		$src = @imagecreatefromstring($raw);
		if ($src === false)
		{
			return $image;
		}

		$srcW = imagesx($src);
		$srcH = imagesy($src);
		if ($srcW < 1 || $srcH < 1)
		{
			imagedestroy($src);
			return $image;
		}

		// Crop the source down to the target aspect ratio first (centered),
		// then resample that crop onto the target canvas - this is the
		// "cover" step, done in source-pixel space so no quality is lost by
		// resizing before cropping.
		$targetRatio = $targetW / $targetH;
		$srcRatio    = $srcW / $srcH;

		if ($srcRatio > $targetRatio)
		{
			// Source is relatively wider than the target - crop its sides.
			$cropH = $srcH;
			$cropW = (int) round($srcH * $targetRatio);
		}
		else
		{
			// Source is relatively taller than the target - crop top/bottom.
			$cropW = $srcW;
			$cropH = (int) round($srcW / $targetRatio);
		}
		$cropX = (int) round(($srcW - $cropW) / 2);
		$cropY = (int) round(($srcH - $cropH) / 2);

		$dst = imagecreatetruecolor($targetW, $targetH);
		if ($dst === false)
		{
			imagedestroy($src);
			return $image;
		}

		// Preserve transparency (e.g. a PNG with an alpha channel) instead of
		// it turning solid black, in case a provider ever returns one.
		imagealphablending($dst, false);
		imagesavealpha($dst, true);
		$transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
		imagefilledrectangle($dst, 0, 0, $targetW, $targetH, $transparent);

		$ok = imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);
		imagedestroy($src);

		if (!$ok)
		{
			imagedestroy($dst);
			return $image;
		}

		ob_start();
		imagepng($dst, null, 6);
		$out = ob_get_clean();
		imagedestroy($dst);

		if ($out === false || $out === '')
		{
			return $image;
		}

		return array('mime' => 'image/png', 'base64' => base64_encode($out));
	}

	static private function _truncateHtml($html, $maxChars)
	{
		$html = (string) $html;
		$maxChars = (int) $maxChars;

		if ($maxChars <= 0 || mb_strlen(trim(strip_tags($html))) <= $maxChars)
		{
			return $html;
		}

		$selfClosing = array('br', 'img', 'hr', 'input');
		$length = mb_strlen($html);
		$visibleCount = 0;
		$openTags = array();
		$result = '';
		$i = 0;

		while ($i < $length && $visibleCount < $maxChars)
		{
			$ch = mb_substr($html, $i, 1);

			if ($ch === '<')
			{
				$tagEnd = mb_strpos($html, '>', $i);
				if ($tagEnd === false)
				{
					break;
				}
				$tag = mb_substr($html, $i, $tagEnd - $i + 1);

				if (preg_match('/^<\s*\/\s*([a-zA-Z0-9]+)/', $tag, $m))
				{
					$name = strtolower($m[1]);
					if (!empty($openTags) && end($openTags) === $name)
					{
						array_pop($openTags);
					}
				}
				else if (preg_match('/^<\s*([a-zA-Z0-9]+)/', $tag, $m))
				{
					$name = strtolower($m[1]);
					if (!in_array($name, $selfClosing, true) && mb_substr(rtrim($tag), -2) !== '/>')
					{
						$openTags[] = $name;
					}
				}

				$result .= $tag;
				$i = $tagEnd + 1;
				continue;
			}

			$result .= $ch;
			$visibleCount++;
			$i++;
		}

		$result = rtrim($result) . '&hellip;';

		while (!empty($openTags))
		{
			$result .= '</' . array_pop($openTags) . '>';
		}

		return $result;
	}

	static private function _parseGeneratedOptions($text, $count)
	{
		$text = trim((string) $text);
		$text = preg_replace('/^```[a-zA-Z]*\s*|\s*```\s*$/', '', $text);

		$blocks = preg_split('/={3,}\s*OPTION[_\s]*\d+\s*={3,}/i', $text);
		$options = array();

		foreach ($blocks as $block)
		{
			$block = trim($block);
			if ($block === '')
			{
				continue;
			}
			if (preg_match('/TITLE:\s*(.*?)\R+SEO_TITLE:\s*(.*?)\R+SEO_DESCRIPTION:\s*(.*?)\R+BODY:\s*(.*)$/is', $block, $m))
			{
				$title = trim($m[1]);
				$body  = trim($m[4]);
				if ($title !== '' && $body !== '')
				{
					$options[] = array(
						'title'            => $title,
						'body'             => $body,
						'meta_title'       => trim($m[2]),
						'meta_description' => trim($m[3]),
					);
				}
			}
			if (count($options) >= $count)
			{
				break;
			}
		}

		if (empty($options))
		{
			// Model didn't follow the requested format at all - fall back to a
			// single option using the full response as the body, rather than
			// failing outright.
			$options[] = array('title' => '', 'body' => $text, 'meta_title' => '', 'meta_description' => '');
		}

		return array('ok' => true, 'options' => $options);
	}

	static private function _openaiChat($model, $system, $user, $apiKey, $maxTokens = null)
	{
		$body = array(
			'model'       => !empty($model) ? $model : 'gpt-4o-mini',
			'messages'    => array(
				array('role' => 'system', 'content' => $system),
				array('role' => 'user', 'content' => $user),
			),
			'temperature' => 0.7,
		);
		if (!empty($maxTokens))
		{
			$body['max_tokens'] = (int) $maxTokens;
		}
		$payload = json_encode($body);

		$resp = self::_post('https://api.openai.com/v1/chat/completions', $payload, array(
			'Content-Type: application/json',
			'Authorization: Bearer ' . $apiKey,
		));

		if ($resp['ok'] !== true)
		{
			return $resp;
		}

		$json    = json_decode($resp['body'], true);
		$content = isset($json['choices'][0]['message']['content']) ? $json['choices'][0]['message']['content'] : '';

		if (trim((string) $content) === '')
		{
			return array('ok' => false, 'text' => __('aiMsgGenerateFailed', true));
		}

		return array('ok' => true, 'text' => $content);
	}

	static private function _anthropicChat($model, $system, $user, $apiKey, $maxTokens = 1600)
	{
		$payload = json_encode(array(
			'model'      => !empty($model) ? $model : 'claude-3-5-sonnet-20241022',
			'max_tokens' => !empty($maxTokens) ? (int) $maxTokens : 1600,
			'system'     => $system,
			'messages'   => array(
				array('role' => 'user', 'content' => $user),
			),
		));

		$resp = self::_post('https://api.anthropic.com/v1/messages', $payload, array(
			'Content-Type: application/json',
			'x-api-key: ' . $apiKey,
			'anthropic-version: 2023-06-01',
		));

		if ($resp['ok'] !== true)
		{
			return $resp;
		}

		$json    = json_decode($resp['body'], true);
		$content = isset($json['content'][0]['text']) ? $json['content'][0]['text'] : '';

		if (trim((string) $content) === '')
		{
			return array('ok' => false, 'text' => __('aiMsgGenerateFailed', true));
		}

		return array('ok' => true, 'text' => $content);
	}

	static private function _googleChat($model, $system, $user, $apiKey, $maxTokens = null)
	{
		$mdl = !empty($model) ? $model : 'gemini-1.5-flash';

		$body = array(
			'system_instruction' => array('parts' => array(array('text' => $system))),
			'contents'           => array(
				array('role' => 'user', 'parts' => array(array('text' => $user))),
			),
		);
		if (!empty($maxTokens))
		{
			$body['generationConfig'] = array('maxOutputTokens' => (int) $maxTokens);
		}
		$payload = json_encode($body);

		$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($mdl)
			 . ':generateContent?key=' . urlencode($apiKey);

		$resp = self::_post($url, $payload, array('Content-Type: application/json'));

		if ($resp['ok'] !== true)
		{
			return $resp;
		}

		$json    = json_decode($resp['body'], true);
		$content = isset($json['candidates'][0]['content']['parts'][0]['text']) ? $json['candidates'][0]['content']['parts'][0]['text'] : '';

		if (trim((string) $content) === '')
		{
			return array('ok' => false, 'text' => __('aiMsgGenerateFailed', true));
		}

		return array('ok' => true, 'text' => $content);
	}

	/**
	 * POST helper shared by the *Chat() methods above.
	 * Returns array('ok' => true, 'body' => <raw response body>) on HTTP 200,
	 * or array('ok' => false, 'text' => <human-readable error>) otherwise.
	 */
	static private function _post($url, $payload, $headers)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 12);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

		$body = curl_exec($ch);
		$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$err  = curl_error($ch);
		curl_close($ch);

		return self::_interpret($code, $err, $body);
	}

	static private function _verifyGet($url, $headers)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 12);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		if (!empty($headers))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		$body = curl_exec($ch);
		$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$err  = curl_error($ch);
		curl_close($ch);

		$result = self::_interpret($code, $err, $body);
		if ($result['ok'] === true)
		{
			return array('valid' => true, 'text' => __('aiVerifyValid', true));
		}
		return array('valid' => false, 'text' => $result['text']);
	}

	/**
	 * Maps a cURL/HTTP result to a uniform ok/error shape.
	 */
	static private function _interpret($code, $err, $body)
	{
		if ($code === 0)
		{
			return array('ok' => false, 'text' => __('aiVerifyConnFail', true) . (!empty($err) ? ' (' . $err . ')' : ''));
		}
		if ($code === 200)
		{
			return array('ok' => true, 'body' => $body);
		}
		$detail = self::_extractErrorDetail($body);
		$suffix = ' (HTTP ' . $code . ')' . ($detail !== '' ? ' - ' . $detail : '');
		if ($code === 401 || $code === 403)
		{
			return array('ok' => false, 'text' => __('aiVerifyRejected', true) . $suffix);
		}
		if ($code === 429)
		{
			return array('ok' => false, 'text' => __('aiVerifyRateLimited', true) . $suffix);
		}
		return array('ok' => false, 'text' => __('aiVerifyUnexpected', true) . $suffix);
	}

	/**
	 * Best-effort extraction of a human-readable error message from a
	 * provider's JSON error body (OpenAI/Anthropic/Google all use an
	 * {"error": {"message": "..."}} shape). Never throws on malformed JSON.
	 */
	static private function _extractErrorDetail($body)
	{
		if (empty($body))
		{
			return '';
		}
		$json = json_decode($body, true);
		if (is_array($json) && isset($json['error']))
		{
			if (is_array($json['error']) && isset($json['error']['message']))
			{
				return substr((string) $json['error']['message'], 0, 300);
			}
			if (is_string($json['error']))
			{
				return substr($json['error'], 0, 300);
			}
		}
		return '';
	}
}
?>

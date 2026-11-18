var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		var $frmCreateCategory = $("#frmCreateCategory"),
			$frmUpdateCategory = $("#frmUpdateCategory"),
			validate = ($.fn.validate !== undefined),
			datagrid = ($.fn.datagrid !== undefined);

		// Names of the language fields (i18n[<language id>][category]); the default language is required
		function categoryRules($form, categoryId) {
			var rules = {}, messages = {},
				defaultName = "i18n[" + myLabel.default_locale + "][category]";
			$form.find("input[name^='i18n['][name$='][category]']").each(function () {
				var $input = $(this),
					locale = $input.attr("lang"),
					name = $input.attr("name"),
					isDefault = (name === defaultName);
				rules[name] = {
					remote: {
						url: "index.php?controller=pjAdminCategories&action=pjActionCheckCategory",
						type: "get",
						data: {
							category: function () {
								return $input.val();
							},
							locale: locale,
							id: categoryId
						}
					}
				};
				messages[name] = {
					remote: myLabel.same_category
				};
				if (isDefault) {
					rules[name].required = true;
					messages[name].required = myLabel.field_required;
				}
			});
			// URL slug of the default language: must be free (the slug fields of the other languages are not checked)
			var slugName = "i18n[" + myLabel.default_locale + "][url_slug]",
				$slug = $form.find("input[name='" + slugName + "']");
			if ($slug.length) {
				rules[slugName] = {
					remote: {
						url: "index.php?controller=pjAdminCategories&action=pjActionCheckSlug",
						type: "get",
						data: {
							url_slug: function () {
								return $slug.val();
							},
							id: categoryId
						}
					}
				};
			}
			return {rules: rules, messages: messages};
		}
		
		if ($frmCreateCategory.length > 0 && validate) {
			var createCfg = categoryRules($frmCreateCategory, 0);
			$frmCreateCategory.validate({
				rules: createCfg.rules,
				messages: createCfg.messages,
				ignore: "",
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				invalidHandler: function (event, validator) {
					// show the language that has the problem
					var $bad = $(validator.errorList[0] ? validator.errorList[0].element : []);
					if ($bad.length > 0) {
						$(".pj-form-langbar-item[data-index='" + $bad.attr("lang") + "']").trigger("click");
					}
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
		}
		if ($frmUpdateCategory.length > 0 && validate) {
			var updateCfg = categoryRules($frmUpdateCategory, $frmUpdateCategory.find("input[name='id']").val());
			$frmUpdateCategory.validate({
				rules: updateCfg.rules,
				messages: updateCfg.messages,
				ignore: "",
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				invalidHandler: function (event, validator) {
					var $bad = $(validator.errorList[0] ? validator.errorList[0].element : []);
					if ($bad.length > 0) {
						$(".pj-form-langbar-item[data-index='" + $bad.attr("lang") + "']").trigger("click");
					}
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
		}
		
		if ($("#grid").length > 0 && datagrid) {
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=pjAdminCategories&action=pjActionUpdate&id={:id}"},
				          {type: "delete", url: "index.php?controller=pjAdminCategories&action=pjActionDeleteCategory&id={:id}"}
				          ],
				columns: [{text: myLabel.category, type: "text", sortable: true, editable: true, width: 530},
				          {text: myLabel.status, type: "select", sortable: true, editable: true, options: [
				                                                                                     {label: myLabel.active, value: "T"}, 
				                                                                                     {label: myLabel.inactive, value: "F"}
				                                                                                     ], applyClass: "pj-status"}],
				dataUrl: "index.php?controller=pjAdminCategories&action=pjActionGetCategory",
				dataType: "json",
				fields: ['category', 'status'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=pjAdminCategories&action=pjActionDeleteCategoryBulk", render: true, confirmation: myLabel.delete_confirmation}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=pjAdminCategories&action=pjActionSaveCategory&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
		}
	});
})(jQuery_1_8_2);
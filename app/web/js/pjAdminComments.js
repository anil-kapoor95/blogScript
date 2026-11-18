var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		var $frmUpdateComment = $("#frmUpdateComment"),
			validate = ($.fn.validate !== undefined),
			datagrid = ($.fn.datagrid !== undefined);

		if ($frmUpdateComment.length > 0 && validate) {
			$frmUpdateComment.validate({
				messages:{
					"name": {
						required: myLabel.field_required
					},
					"email": {
						required: myLabel.field_required,
						email: myLabel.invalid_email
					},
					"comment": {
						required: myLabel.field_required
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
		}
		function formatPosts (str, obj) {
			return '<a href="index.php?controller=pjAdminPosts&action=pjActionUpdate&id='+obj.post_id+'">'+str+'</a>';
		}
		if ($("#grid").length > 0 && datagrid) {
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=pjAdminComments&action=pjActionUpdate&id={:id}"},
				          {type: "delete", url: "index.php?controller=pjAdminComments&action=pjActionDeleteComment&id={:id}"}
				          ],
				columns: [{text: myLabel.date_published, type: "text", sortable: true, editable: false, width: 120},
				          {text: myLabel.name, type: "text", sortable: true, editable: true, width: 100},
				          {text: myLabel.under_post, type: "text", sortable: true, editable: false, width: 230, renderer: formatPosts},
				          {text: myLabel.status, type: "select", sortable: true, editable: true, width: 140, options: [
				                                                                                     {label: myLabel.active, value: "T"}, 
				                                                                                     {label: myLabel.waiting, value: "W"},
				                                                                                     {label: myLabel.inactive, value: "F"}
				                                                                                     ], applyClass: "pj-status"}],
				dataUrl: "index.php?controller=pjAdminComments&action=pjActionGetComment" + pjGrid.queryString,
				dataType: "json",
				fields: ['created', 'name', 'title', 'status'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=pjAdminComments&action=pjActionDeleteCommentBulk", render: true, confirmation: myLabel.delete_confirmation},
					   {text: myLabel.status_to_approved, url: "index.php?controller=pjAdminComments&action=pjActionStatusComment&status=T", render: true},
					   {text: myLabel.status_to_not_approved, url: "index.php?controller=pjAdminComments&action=pjActionStatusComment&status=F", render: true},
					   {text: myLabel.exported, url: "index.php?controller=pjAdminComments&action=pjActionExportComment", ajax: false}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=pjAdminComments&action=pjActionSaveComment&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
		}
		
		$(document).on("click", ".btn-all", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).addClass("pj-button-active").siblings(".pj-button").removeClass("pj-button-active");
			var content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				status: "",
				q: ""
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=pjAdminComments&action=pjActionGetComment", "created", "DESC", content.page, content.rowCount);
			return false;
		}).on("click", ".btn-filter", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache"),
				obj = {};
			$this.addClass("pj-button-active").siblings(".pj-button").removeClass("pj-button-active");
			obj.status = "";
			obj[$this.data("column")] = $this.data("value");
			$.extend(cache, obj);
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=pjAdminComments&action=pjActionGetComment", "created", "DESC", content.page, content.rowCount);
			return false;
		}).on("submit", ".frm-filter", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				q: $this.find("input[name='q']").val()
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=pjAdminComments&action=pjActionGetComment", "created", "DESC", content.page, content.rowCount);
			return false;
		});
	});
})(jQuery_1_8_2);
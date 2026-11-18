var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		var $frmCreateCategory = $("#frmCreateCategory"),
			$frmUpdateCategory = $("#frmUpdateCategory"),
			validate = ($.fn.validate !== undefined),
			datagrid = ($.fn.datagrid !== undefined);

		if ($frmCreateCategory.length > 0 && validate) {
			$frmCreateCategory.validate({
				rules: {
					"category": {
						required: true,
						remote: "index.php?controller=pjAdminCategories&action=pjActionCheckCategory"
					},
					"url_slug": {
						remote: "index.php?controller=pjAdminCategories&action=pjActionCheckSlug&id=0"
					}
				},
				messages:{
					"category": {
						required: myLabel.field_required,
						remote: myLabel.same_category
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
		if ($frmUpdateCategory.length > 0 && validate) {
			$frmUpdateCategory.validate({
				rules: {
					"category": {
						required: true,
						remote: "index.php?controller=pjAdminCategories&action=pjActionCheckCategory&id=" + $frmUpdateCategory.find("input[name='id']").val()
					},
					"url_slug": {
						remote: "index.php?controller=pjAdminCategories&action=pjActionCheckSlug&id=" + $frmUpdateCategory.find("input[name='id']").val()
					}
				},
				messages:{
					"category": {
						required: myLabel.field_required,
						remote: myLabel.same_category
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
jQuery(document).ready(function ($) {

	$(".hybrid-select").select2({
		placeholder: "Choose From Dropdown",
	});

	$(".job-ids-dropdown").select2({
		placeholder: "Job Ids.",
	});

	$(".clients-dropdown").select2({
		placeholder: "Clients",
	});

	$(".keywords-dropdown").select2({
		placeholder: "Keywords",
	});

	$(".limits-dropdown").select2({
		placeholder: "Limit",
	});

	$("#ProductSurveyForm").validate({
		rules: {
			keyword: {
				required: true,
				maxlength: 255
			},
			name: {
				required: true,
				maxlength: 255
			},
			order_number: {
				required: true,
				maxlength: 255
			},
			amount: {
				required: true,
				number: true
			},
			email: {
				required: true,
				email: true,
				maxlength: 255
			},
			confirm_email: {
				required: true,
				email: true,
				maxlength: 255,
				equalTo: "#email"
			},
			phone: {
				required: true,
				number: true,
				minlength: 10,
				maxlength: 20
			}
		},
		errorPlacement: function (error, element) {
			error.appendTo(element.closest(".form-group").find(".validation-box"));
		},
		errorClass: "form-validation-error",
		submitHandler: function (form) {
			form.submit();
			$('#submit_admin_product_survey').attr('disabled', 'disabled');
		}
	});

	$('#tos-status-checkbox').on('change', function (e) {
		e.preventDefault();
		if ($('#tos-status-checkbox').is(':checked')) {
			$('#submit_admin_product_survey').prop('disabled', false);
		} else {
			$('#submit_admin_product_survey').prop('disabled', true);
		}
	});

	var scntDiv = $('#p_scents');

	$('#addScnt').on('click', function () {
		$('<p><label for="p_scnts"><input style="width:80%" type="text" id="asin_number" size="20" name="asin_number[]" value=""/></label> <a href="#" class="remScnt" onclick=\'jQuery(this).parents("p").remove()\'>Remove</a></p>')
			.appendTo($(scntDiv));
		return false;
	});

	$('.remScnt').on('click', function () {
		$(this).parents('p').remove();
	});
});

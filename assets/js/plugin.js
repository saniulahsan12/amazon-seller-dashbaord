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
		$('<p><label for="p_scnts"><input placeholder="ASIN" style="width: 15%;" type="text" id="asin_number" name="asin_number[]"/><input placeholder="Tag" style="width: 50%;" type="text" id="asin_category" name="asin_category[]"/><select style="width: 15%;" id="asin_percentage" name="asin_percentage[]"> <option value="5">5%</option> <option value="10">10%</option> <option value="15">15%</option> <option value="20">20%</option> <option value="25">25%</option><option value="30">30%</option><option value="35">35%</option><option value="40">40%</option><option value="45">45%</option><option value="50">50%</option><option value="55">55%</option><option value="60">60%</option><option value="65">65%</option><option value="70">70%</option><option value="75">75%</option><option value="80">80%</option><option value="85">85%</option><option value="90">90%</option><option value="95">95%</option><option value="100">100%</option></select></label><button type="button" class="remScnt button button-primary button-large"><span style="margin-top: 6px;" onclick=\'jQuery(this).parents("p").remove()\' class="dashicons dashicons-no-alt"></span></button></p>')
			.appendTo($(scntDiv));
		return false;
	});

	$('.remScnt').on('click', function () {
		$(this).parents('p').remove();
	});
});

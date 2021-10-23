jQuery(document).ready(function($) {
    $("#ProductSurveyForm").validate({
			rules: {
				product: {
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
                    minlength : 10,
                    maxlength : 20
				}
			},
			errorPlacement: function(error, element) {
				error.appendTo(element.closest(".form-group").find(".validation-box"));
			},
			// messages: {
			// 	subjectName: {
			// 		required: "{LT code='REQUIRED_VALIDATION'}",
			// 		maxlength: "{LT code='MAXIMUM_LABEL'} 512 {LT code='CHARACTER_LABEL'}"
			// 	},
			// 	teacherName: {
			// 		required: "{LT code='REQUIRED_VALIDATION'}",
			// 		maxlength: "{LT code='MAXIMUM_LABEL'} 255 {LT code='CHARACTER_LABEL'}"
			// 	},
			// 	materialDescription: {
			// 		maxlength: "{LT code='MAXIMUM_LABEL'} 2048 {LT code='CHARACTER_LABEL'}"
			// 	},
			// 	materialPublishTime: {
			// 		required: '{LT code="REQUIRED_VALIDATION"}',
			// 		isValidDate: '{LT code="DATE_VALIDATION"}',
			// 		maxlength: "{LT code='MAXIMUM_LABEL'} 16 {LT code='CHARACTER_LABEL'}"
			// 	},
			// 	termsOfUseLink: {
			// 		url: "{LT code='URL_VALIDATION'}"
			// 	},
			// 	materialUrl: {
			// 		required: "{LT code='REQUIRED_VALIDATION'}",
			// 		extensionCheck: "{LT code='VIDEO_VALIDATION'}",
			// 		filesize: "{LT code='MAXIMUM_LABEL'} 1 {LT code='FILE_LABEL'}"
			// 	},
			// 	materialCover: {
			// 		extensionCheckImage: "{LT code='IMAGE_VALIDATION'}"
			// 	}
			// },
			errorClass: "form-validation-error",
			submitHandler: function(form) {
				form.submit();
				$('#submit_admin_product_survey').attr('disabled','disabled');
			}
		});
});
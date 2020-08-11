var User = {
	google_login: function () {
		$("#googleButton").attr("class", "fa fa-spinner fa-spin");
		window.location = "/rest/login";
	},

	login: function (selector) {
		$(selector).validate({
			submitHandler: function (form, event) {
				event.preventDefault();
				$("#singinButton").attr("class", "fa fa-spinner fa-spin");
				Utils.http_post(
					"rest/login/company",
					selector,
					function (data) {
						var user = Utils.parseJwt(data.token);
						if (user.type == "COMPANY_USER") {
							if (user.company_status == "PENDING") {
								toastr.warning("Waiting for staff verify your company");
							} else if (user.company_status == "DENIED") {
								toastr.error("Your company was rejected");
							} else if (user.company_status == null) {
								localStorage.setItem("userToken", data.token);
								toastr.warning("Please enter company info");
								$("#company_large_modal .modal-body").load("remote_modals/add_company_modal.html", function () {
									$("#company_large_modal").modal("show");
								});
							} else {
								localStorage.setItem("userToken", data.token);
								Utils.user_login_check("index.html");
							}
							$("#singinButton").attr("class", "");
						} else {
							localStorage.setItem("userToken", data.token);
							Utils.user_login_check("index.html");
						}
					},
					null,
					function (error) {
						$("#singinButton").attr("class", "");
						error_response = JSON.parse(error.responseText);
						toastr.error(error_response.msg);
						console.log(error_response.login_attempt);
						if (error_response.login_attempt >= 3) {
							$("#login_captcha").removeAttr("hidden");
							hcaptcha.reset(Company.getWidgetIDLogin());
						}
					}
				);
			},
		});
	},
	signup: function (selector) {
		$(selector).validate({
			rules: {
				name: {
					required: true,
					minlength: 5,
					maxlength: 50,
				},

				email: {
					required: true,
				},

				password: {
					required: true,
					minlength: 4,
					maxlength: 30,
				},

				rpassword: {
					required: true,
					equalTo: "#password",
				},
			},

			messages: {
				fullname: {
					minlength: "Your name must be at least 5 characters long",
					maxlength: "Your name must be less than 50 characters long",
				},

				password: {
					minlength: "The password must be longer than 4",
					maxlength: "The password must be shorter than 30",
				},

				rpassword: {
					equalTo: "Enter Confirm Password Same as Password",
				},
			},

			submitHandler: function (form, event) {
				event.preventDefault();
				$("#singupButton").attr("class", "fa fa-spinner fa-spin");
				var user = Utils.objectifyForm($(selector));
				user["type"] = "COMPANY_USER";
				Utils.http_post(
					"rest/register",
					selector,
					function () {
						toastr.success("Successfully registered, now please enter company info");
						$("#company_large_modal .modal-body").load("remote_modals/add_company_modal.html", function () {
							$("#company_large_modal").modal("show");
						});
						Utils.http_post(
							"rest/login/company",
							null,
							function (data) {
								$("#singupButton").attr("class", "");
								localStorage.setItem("userToken", data.token);
							},
							{ email: $("#email").val(), password: $("#password").val() },
							function (error) {
								error_response = JSON.parse(error.responseText);
								toastr.error(error_response.msg);
							}
						);
					},
					user,
					function (error) {
						$("#singupButton").attr("class", "");
						error_response = JSON.parse(error.responseText);
						toastr.error(error_response.msg);
						hcaptcha.reset(Company.getWidgetIDRegister());
					}
				);
			},
		});
	},

	frogot_password: function () {
		$("#frogot_password_form").validate({
			rules: {
				email: {
					required: true,
				},
			},

			submitHandler: function (form, event) {
				event.preventDefault();
				$("#requestButton").attr("class", "fa fa-spinner fa-spin");
				Utils.http_post(
					"rest/users/send-mail",
					"#frogot_password_form",
					function () {
						$("#requestButton").attr("class", "");
						toastr.success("Recovery mail has been send to the provided email");
					},
					null,
					function (error) {
						$("#requestButton").attr("class", "");
						error_response = JSON.parse(error.responseText);
						toastr.error(error_response.msg);
					}
				);
			},
		});
	},

	recover_password: function () {
		$("#update_password").validate({
			rules: {
				password: {
					required: true,
					minlength: 4,
					maxlength: 20,
				},

				rpassword: {
					required: true,
					equalTo: "#password",
				},
			},

			messages: {
				password: {
					minlength: "The password must be longer than 4",
					maxlength: "The password must be shorter than 10",
				},

				rpassword: {
					equalTo: "Enter Confirm Password Same as Password",
				},
			},

			submitHandler: function (form, event) {
				event.preventDefault();
				$("#recoverButton").attr("class", "fa fa-spinner fa-spin");
				user = Utils.parseJwt(localStorage.getItem("userToken"));
				Utils.http_put(
					"rest/users/password-recover/" + user.id,
					"#update_password",
					function () {
						localStorage.setItem("userToken", "");
						window.location = "/";
					},
					null,
					function (error) {
						$("#recoverButton").attr("class", "");
						error_response = JSON.parse(error.responseText);
						toastr.error(error_response.msg);
					}
				);
			},
		});
	},
};

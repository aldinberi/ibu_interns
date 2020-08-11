var Company = {
	widgetID_login: null,

	widgetID_register: null,

	setWidgetIDLogin: function (widgetID_login) {
		this.widgetID_login = widgetID_login;
	},

	setWidgetIDRegister: function (widgetID_register) {
		this.widgetID_register = widgetID_register;
	},

	getWidgetIDLogin: function () {
		return this.widgetID_login;
	},

	getWidgetIDRegister: function () {
		return this.widgetID_register;
	},
	dropdown: function (selector) {
		Utils.http_get("rest/companies", function (data) {
			var options = "";
			for (i = 0; i < data.length; i++) {
				options += "<option value= '" + data[i].id + "'>" + data[i].name + "</option>";
			}
			$(selector).html(options);
			$(selector).select2({
				placeholder: "Select a company",
				allowClear: true,
			});
		});
	},

	get_all: function (status) {
		if (jQuery.active == 0) {
			$('a[name="status"]').attr("style", "pointer-events: none; cursor: default");
			$.fn.dataTable.ext.errMode = "none";
			$("#company_table").DataTable().clear().destroy();
			var table = $("#company_table").DataTable({
				responsive: true,
				processing: true,
				serverSide: true,
				ajax: {
					url: "rest/companies/datatables?status=" + status,
					type: "GET",
					headers: {
						Bearer: localStorage.getItem("userToken"),
					},
					error: function (xhr, ajaxOptions, thrownError) {
						toastr.error("Error occured: " + thrownError);
					},
				},
				columns: [{ data: "id" }, { data: "name" }, { data: "status" }, { data: "id" }],
				columnDefs: [
					{
						targets: 2,
						render: function (data, type, full, meta) {
							if (data == "PENDING") {
								return '<span class="m-badge m-badge--warning m-badge--wide">PENDING</span>';
							} else if (data == "APPROVED") {
								return '<span class="m-badge m-badge--info m-badge--wide">APPROVED</span>';
							} else if (data == "COMPLETED") {
								return '<span class="m-badge m-badge--metal m-badge--wide">COMPLETED</span>';
							} else if (data == "ACTIVE") {
								return '<span class="m-badge m-badge--success m-badge--wide">ACTIVE</span>';
							} else if (data == "DENIED") {
								return '<span class="m-badge m-badge--danger m-badge--wide">DENIED</span>';
							}
							if (data == "undefined") {
								return data;
							}
						},
					},
					{
						targets: 3,
						render: function (data, type, full, meta) {
							return (
								'<a class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air" onclick="Company.get_by_id(' +
								data +
								')"> <span> <i class="la la-plus"></i><span>More</span></span></a>'
							);
						},
					},
				],
			});

			table.one("xhr", function (e, settings, json) {
				$('a[name="status"]').removeAttr("style");
			});

			table.column(0).visible(false);
		}
	},

	tablist_selection: function (tabs) {
		var tabString = "";
		var queue = [];
		for (var i = 0; i < tabs.length; i++) {
			if (tabs[i].localeCompare("PENDING") == 0) {
				tabString +=
					"<li class='nav-item'><a id =\"statusPending\" class='nav-link pending-tab' href='' data-toggle='tab' name='status'" +
					"onclick=\"Company.get_all('PENDING');\">Pending</a></li>";
				queue.push("statusPending");
			} else if (tabs[i].localeCompare("APPROVED") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusApproved" class="nav-link approved-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Company.get_all('APPROVED');\">Approved</a></li>";
				queue.push("statusApproved");
			} else if (tabs[i].localeCompare("DENIED") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusDenied" class="nav-link denied-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Company.get_all('DENIED');\">Denied</a></li>";
				queue.push("statusDenied");
			}
		}

		$("#company_tablist").html(tabString);
		$("#" + queue.shift()).addClass("active");
	},

	get_by_id: function (id) {
		var button_str = "";
		$("#company_small_modal .modal-body").load("remote_modals/company_info_modal.html", function () {
			$("#company_small_modal").modal("toggle");
			setTimeout(function () {
				Utils.block("#company_small_modal .modal-content", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				Utils.http_get(
					"rest/companies/" + id,
					function (data) {
						console.log(data);
						for (var property in data) {
							if (data.hasOwnProperty(property)) {
								$("#company_modal_" + property).text(data[property]);
							}
						}
						if (data.status == "PENDING") {
							button_str +=
								'<button type="button" id="companyDenyButton" onclick="Company.update_status(' +
								data.id +
								', \'DENIED\')" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air btn-danger">Deny</button>  ';
							button_str +=
								'<button type="button" id="companyApproveButton" onclick="Company.update_status(' +
								data.id +
								', \'APPROVED\')" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air">Approve</button>  ';
						} else if (data.status == "DENIED") {
							button_str +=
								'<button type="button" id="companyApproveButton" onclick="Company.update_status(' +
								data.id +
								', \'APPROVED\')" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air">Approve</button>  ';
							button_str +=
								'<button class="btn btn-outline-focus  m-btn m-btn--pill m-btn--custom m-btn--air" data-dismiss="modal">Cancel</button>';
						} else {
							button_str +=
								'<button class="btn btn-outline-focus  m-btn m-btn--pill m-btn--custom m-btn--air" data-dismiss="modal">Cancel</button>';
						}
						$("#company_more_buttons").html(button_str);
						Utils.unblock("#company_small_modal .modal-content");
					},
					function (error) {
						error_text = JSON.parse(error.responseText);
						toastr.error(error_text.msg);
						Utils.unblock("#company_small_modal .modal-content");
					}
				);
			}, 300);
		});
	},

	update_status: function (id, status) {
		Utils.block("#company_small_modal .modal-content", {
			overlayColor: "#000000",
			type: "loader",
			state: "info",
			size: "lg",
		});
		Utils.http_put(
			"rest/companies/" + id,
			null,
			function () {
				table = $("#company_table").DataTable();
				table.clear();
				table.draw();
				toastr.success("Successfully updated status");
				Utils.unblock("#company_small_modal .modal-content");
				$("#company_small_modal").modal("toggle");
			},
			{ status: status },
			function (error) {
				error_text = JSON.parse(error.responseText);
				toastr.error(error_text.msg);
				Utils.unblock("#company_small_modal .modal-content");
			}
		);
	},

	load: function () {
		Company.get_all("PENDING");
		Company.tablist_selection(["PENDING", "APPROVED", "DENIED"]);
	},

	update_intenship_status: function (internship_id, intern_id, status) {
		Utils.block("#interns_small_modal .modal-content", {
			overlayColor: "#000000",
			type: "loader",
			state: "info",
			size: "lg",
		});
		Utils.http_put(
			"rest/internship/interns",
			null,
			function () {
				toastr.success("Successfully updated status");
				$("#interns_small_modal").modal("toggle");
				Utils.unblock("#interns_small_modal .modal-content");
				table = $("#interns_table").DataTable();
				table.clear();
				table.draw();
			},
			{ internship_id: internship_id, intern_id: intern_id, status: status },
			function (error) {
				error_text = JSON.parse(error.responseText);
				toastr.error(error_text.msg);
				Utils.unblock("#interns_small_modal .modal-content");
			}
		);
	},

	init: function () {
		$("body").on("click", '[data-toggle="modal"]', function () {
			$($(this).data("target") + " .modal-body").load($(this).data("remote"));
		});
		$("body").on("hidden.bs.modal", ".modal", function () {
			$(this).find(".modal-body").empty();
		});
		if (!localStorage.getItem("userToken")) {
			var token_jwt = window.location.href.split("?jwt=")[1];
			var token_message = window.location.href.split("?message=")[1];
			if (token_jwt) {
				localStorage.setItem("userToken", token_jwt);
				Utils.user_login_check("index.html");
			} else if (token_message) {
				token_message = decodeURI(token_message);
				console.log(token_message);
				toastr.error(token_message);
			}
		}
		Company.validate_company();
		User.frogot_password();

		$(window).resize(function () {
			Utils.rescaleCaptcha("#register_captcha");
			Utils.rescaleCaptcha("#login_captcha");
		});

		User.login("#user_login");
		User.signup("#user_signup");
		Utils.user_login_check("index.html");
		// Utils.rescaleCaptcha("#register_captcha");
		// Utils.rescaleCaptcha("#login_captcha");
	},

	recovery_init: function () {
		var token_jwt = window.location.href.split("?jwt=")[1];
		if (token_jwt) {
			localStorage.setItem("userToken", token_jwt);
		}

		User.recover_password();
	},

	grade_internship: function (id, intern_id) {
		if ($("#grade_form").valid()) {
			var grade = Utils.objectifyForm($("#grade_form"));
			grade = JSON.stringify(grade);
			Utils.block("#internship_large_modal .modal-content", {
				overlayColor: "#000000",
				type: "loader",
				state: "info",
				size: "lg",
			});
			Utils.http_put(
				"rest/internships/" + id,
				null,
				function () {
					table = $("#internship_table").DataTable();
					table.clear();
					table.draw();
					toastr.success("Successfully graded");
					Utils.unblock("#grade_modal .modal-content");
					$("#internship_large_modal").modal("toggle");
					$("#internship_small_modal").modal("toggle");
				},
				{ grade: grade, status: "COMPLETED", intern_id: intern_id },
				function (error) {
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
					Utils.unblock("#internship_large_modal .modal-content");
				}
			);
		}
	},

	validate_grade: function () {
		$("#grade_form").validate({
			rules: {
				student_name: {
					required: true,
					minlength: 5,
					maxlength: 50,
				},

				student_department: {
					required: true,
					minlength: 5,
					maxlength: 50,
				},
				student_id: {
					required: true,
				},
				company_name: {
					required: true,
					minlength: 3,
					maxlength: 20,
				},
				branch_name: {
					required: true,
					minlength: 2,
					maxlength: 30,
				},

				company_department: {
					required: true,
					minlength: 2,
					maxlength: 30,
				},
				grade_start_date: {
					required: true,
				},
				grade_end_date: {
					required: true,
				},
				// attendence: {
				// 	required: true
				// },

				"attendence[]": { required: true },
				obedience: {
					required: true,
				},
				work_knowledge: {
					required: true,
				},
				willingness_to_learn: {
					required: true,
				},
				new_concepts_into_practice: {
					required: true,
				},
				responsibility: {
					required: true,
				},
				own_initiative: {
					required: true,
				},
				orderliness: {
					required: true,
				},
				outfit: {
					required: true,
				},
				communication_customers: {
					required: true,
				},
				communication_colleagues: {
					required: true,
				},
				competence: {
					required: true,
				},
				overall: {
					required: true,
				},
				opinion: {
					required: true,
					minlength: 5,
					maxlength: 500,
				},

				accept_again: {
					required: true,
					minlength: 5,
					maxlength: 500,
				},
			},

			messages: {
				student_name: {
					minlength: "The student's name must be at least 5 characters long",
					maxlength: "The student's name must be less than 50 characters long",
				},
				student_department: {
					minlength: "The student department must be at least 5 characters long",
					maxlength: "The student department must be less than 50 characters long",
				},
				company_name: {
					minlength: "The company name must be at least 3 characters long",
					maxlength: "The company name must be less than 50 characters long",
				},
				branch_name: {
					minlength: "The branch name must be at least 2 characters long",
					maxlength: "The branch name must be less than 50 characters long",
				},
				company_department: {
					minlength: "The company department must be at least 2 characters long",
					maxlength: "The company department must be less than 50 characters long",
				},
				opinion: {
					minlength: "The answer must be at least 5 characters long",
					maxlength: "The answer must be less than 50 characters long",
				},
				accept_again: {
					minlength: "The answer must be at least 5 characters long",
					maxlength: "The answer must be less than 50 characters long",
				},
			},
		});
	},

	validate_company: function () {
		$("#add_company_form").validate({
			rules: {
				name: {
					required: true,
					minlength: 5,
					maxlength: 50,
				},

				email: {
					required: true,
				},
				address: {
					required: true,
					minlength: 5,
					maxlength: 50,
				},
				phone: {
					required: true,
					minlength: 9,
					maxlength: 20,
				},
				website: {
					required: true,
					minlength: 5,
					maxlength: 30,
				},
			},

			messages: {
				name: {
					minlength: "Your name must be at least 5 characters long",
					maxlength: "Your name must be less than 50 characters long",
				},

				address: {
					minlength: "The address must be longer than 5",
					maxlength: "The address must be shorter than 50",
				},

				phone: {
					minlength: "The phone number must be longer than 9",
					maxlength: "The phone number must be shorter than 20",
				},

				website: {
					minlength: "The website must be longer than 5",
					maxlength: "The website must be shorter than 30",
				},
			},
		});
	},

	post_company: function () {
		if ($("#add_company_form").valid()) {
			Utils.block("#company_large_modal .modal-content", {
				overlayColor: "#000000",
				type: "loader",
				state: "info",
				size: "lg",
			});
			Utils.http_post(
				"rest/companies",
				"#add_company_form",
				function () {
					toastr.success("Successfully registerd company, waiting for verification from staff");
					Utils.unblock("#company_large_modal .modal-content");
					$("#company_large_modal").modal("hide");
					$("#singupButton").attr("class", "");
				},
				null,
				function (error) {
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
					Utils.unblock("#company_large_modal .modal-content");
				}
			);
		}
	},
};

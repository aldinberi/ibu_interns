var Internship = {
	user: Utils.parseJwt(localStorage.getItem("userToken")),

	dropdown: function (url, selector, callBack, errorBack) {
		Utils.http_get(
			url,
			function (data) {
				var options = "<option></option>";
				for (i = 0; i < data.length; i++) {
					options += "<option value= '" + data[i].id + "'>" + data[i].title + "</option>";
				}
				$(selector).html(options);
				$(selector).select2({
					placeholder: "Select a option",
				});
				if (callBack) {
					callBack();
				}
			},
			function (error) {
				if (errorBack) {
					errorBack(error);
				} else {
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
				}
			}
		);
	},

	add: function () {
		$("#internship_large_modal .modal-body").load("remote_modals/add_internship_modal.html", function () {
			$("#start_date").datepicker({
				format: "yyyy-mm-dd",
				autoclose: true,
			});

			$("#end_date").datepicker({
				format: "yyyy-mm-dd",
				autoclose: true,
			});
			buttonString =
				"<button class='btn btn-outline-focus  m-btn m-btn--pill m-btn--custom' data-dismiss='modal'>Cancel</button> " +
				"<button type='submit' onclick= \"Internship.insert('#add_internship_form');\" class='btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air'>Save</button> ";

			$("#internship_large_modal").modal("show");
			setTimeout(function () {
				Utils.block("#internship_large_modal .modal-content", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				Internship.department_selection(
					function () {
						Utils.unblock("#internship_large_modal .modal-content");
					},
					function (error) {
						error_text = JSON.parse(error.responseText);
						toastr.error(error_text.msg);
						Utils.unblock("#internship_large_modal .modal-content");
					}
				);
			}, 300);
			$("#internship_add_buttons").html(buttonString);

			//Company.dropdown("#company_id");
		});
	},

	get_all: function (selector, status, company_id, department_id, intern_id, intern_status) {
		if (jQuery.active == 0) {
			$('a[name="status"]').attr("style", "pointer-events: none; cursor: default");

			var url = "rest/internships/datatables?";

			if (status) {
				url += "&status=" + status;
			}

			if (company_id) {
				url += "&company_id=" + company_id;
			}

			if (department_id) {
				url += "&department_id=" + department_id;
			}

			if (intern_id) {
				url += "&intern_id=" + intern_id;
			}

			if (intern_status) {
				url += "&intern_status=" + intern_status;
			}

			$.fn.dataTable.ext.errMode = "none";

			$(selector).DataTable().clear().destroy();
			var table = $(selector).DataTable({
				responsive: true,
				processing: true,
				serverSide: true,
				ajax: {
					url: url,
					type: "GET",
					headers: {
						Bearer: localStorage.getItem("userToken"),
					},
					error: function (xhr, ajaxOptions, thrownError) {
						error_response = JSON.parse(xhr.responseText);
						toastr.error(error_response.msg);
					},
				},
				columns: [
					{ data: "id" },
					{ data: "company_name" },
					{ data: "department_name" },
					{ data: "company_id" },
					{ data: {} },
					{ data: "title" },
					{ data: "start_date" },
					{ data: "end_date" },
					{ data: "status" },
					{ data: "id" },
					{ data: "id" },
				],
				columnDefs: [
					{
						targets: 3,
						render: function (data, type, full, meta) {
							return (
								'<button type="button" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air" data-toggle="modal" data-remote="remote_modals/mentor_modal.html" data-target="#internship_small_modal" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air"  onclick="Internship.get_mentor_id(' +
								"'#internship_small_modal', " +
								data +
								')"> <span> <i class="la la-plus"></i> <span>More</span></span></button>'
							);
						},
					},
					{
						targets: 4,
						render: function (data, type, full, meta) {
							if (data.intern_id) {
								return (
									'<button onclick="Internship.get_intern_id(' +
									data.intern_id +
									", " +
									data.id +
									')"class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air"> <span> <i class="la la-plus"></i> <span>More</span></span></button>'
								);
							} else {
								return '<span class="m-badge m-badge--metal m-badge--wide">NO INTERN AVALIABLE</span>';
							}
						},
					},
					{
						targets: 8,
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
						targets: 10,
						render: function (data, type, full, meta) {
							return (
								'<a class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air" onclick="Internship.get_by_id(' +
								data +
								')"> <span> <i class="la la-plus"></i> <span>More</span></span></a>'
							);
						},
					},
					{
						targets: 9,
						render: function (data, type, full, meta) {
							if (data) {
								return (
									"<a class='btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air' onclick=\"Internship.get_grade('#internship_large_modal', " +
									data +
									')"> <span> <i class="la la-plus"></i> <span>More</span></span></a>'
								);
							} else {
								return '<span class="m-badge m-badge--metal m-badge--wide">NO GRADE AVALIABLE</span>';
							}
						},
					},
				],
			});

			if (status != "COMPLETED") {
				table.column(9).visible(false);
			}

			if (Internship.user.type.localeCompare("COMPANY_USER") == 0) {
				table.column(0).visible(false);
				table.column(1).visible(false);
				if (status != "ACTIVE" && status != "COMPLETED") {
					table.column(4).visible(false);
				}
			} else if (Internship.user.type.localeCompare("INTERN") == 0) {
				table.column(0).visible(false);
				table.column(2).visible(false);
				table.column(4).visible(false);
				table.column(8).visible(false);
				table.column(9).visible(false);
			} else if (Internship.user.type.localeCompare("PROFESSOR") == 0) {
				table.column(0).visible(false);
				if (status != "ACTIVE" && status != "COMPLETED") {
					table.column(4).visible(false);
				}
			}

			table.one("xhr", function (e, settings, json) {
				$('a[name="status"]').removeAttr("style");
			});
		}
	},

	get_intern_id: function (intern_id, internship_id) {
		$("#internship_small_modal .modal-body").load("remote_modals/intern_info_modal.html", function () {
			var documents_str = "";
			$("#internship_small_modal").modal("toggle");
			setTimeout(function () {
				Utils.block("#internship_small_modal .modal-content", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				Utils.http_get(
					"rest/users/intern/" + intern_id + "/internship/" + internship_id,
					function (data) {
						buttonString =
							' <button type="button" class="btn btn-outline-focus  m-btn m-btn--pill m-btn--custom m-btn--air" data-dismiss="modal">Cancel</button>';
						$("#intern_modal_name").text(data[0].name);
						if (data[0].year == 3) {
							$("#intern_modal_year").text("Third");
						} else if (data[0].year == 2) {
							$("#intern_modal_year").text("Second");
						} else {
							$("#intern_modal_year").text("First");
						}
						$("#intern_modal_department").text(data[0].department);
						if (data[0].documents_id != null) {
							documents_str +=
								"<button type='button' class='btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air' onclick='Interns.download_files(" +
								data[0].documents_id +
								")'> <span>Download files</span></button>";
						} else {
							documents_str += "<span>No documents were attached</span>";
						}
						$("#documents_download").html(documents_str);
						$("#interns_more_buttons").html(buttonString);
						$("#interns_more_buttons").removeAttr("hidden");
						Utils.unblock("#internship_small_modal .modal-content");
					},
					function (error) {
						error_text = JSON.parse(error.responseText);
						toastr.error(error_text.msg);
						Utils.unblock("#internship_small_modal .modal-content");
					}
				);
			}, 300);
		});
	},
	update_internship: function (id) {
		let data = Utils.objectifyForm($("#add_internship_form"));
		data.department_id = parseInt(data.department_id);
		Internship.validate("#add_internship_form", function () {
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
					toastr.success("Successfully updated internship, , waiting for verification from staff");
					Utils.unblock("#internship_large_modal .modal-content");
					$("#internship_large_modal").modal("toggle");
				},
				data,
				function (error) {
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
					Utils.unblock("#internship_large_modal .modal-content");
				}
			);
		});
	},

	delete_internship: function (id) {
		Utils.block("#internship_large_modal .modal-content", {
			overlayColor: "#000000",
			type: "loader",
			state: "info",
			size: "lg",
		});
		Utils.http_delete(
			"rest/internships/" + id,
			function () {
				table = $("#internship_table").DataTable();
				table.clear();
				table.draw();
				toastr.success("Successfully deleted internship");
				Utils.unblock("#internship_large_modal .modal-content");
				$("#internship_large_modal").modal("toggle");
			},
			function (error) {
				error_text = JSON.parse(error.responseText);
				toastr.error(error_text.msg);
				Utils.unblock("#internship_large_modal .modal-content");
			}
		);
	},

	update_status: function (id, status) {
		Utils.block("#internship_small_modal .modal-content", {
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
				for (i = table.rows().count() - 1; i >= 0; i--) {
					if (table.cell(i, 0).data() == id) {
						table.row(i).remove();
					}
				}
				table.draw();
				toastr.success("Successfully updated status");
				Utils.unblock("#internship_small_modal .modal-content");
				$("#internship_small_modal").modal("toggle");
			},
			{ status: status },
			function (error) {
				error_text = JSON.parse(error.responseText);
				toastr.error(error_text.msg);
				Utils.unblock("#internship_small_modal .modal-content");
			}
		);
	},

	get_grade: function (selector, id) {
		$(selector + " .modal-body").load("remote_modals/grade_modal.html", function () {
			$("#grade_start_date").datepicker({
				format: "yyyy-mm-dd",
				autoclose: true,
			});

			$("#grade_end_date").datepicker({
				format: "yyyy-mm-dd",
				autoclose: true,
			});
			$(selector).modal("toggle");
			setTimeout(function () {
				Utils.block(selector + " .modal-content", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				Utils.http_get(
					"rest/internships/" + id,
					function (data) {
						$("#grade_modal_button").hide();
						var grade = JSON.parse(data.grade);
						for (var property in grade) {
							if (grade.hasOwnProperty(property)) {
								$('#grade_form input[name="' + property + '"]').val(grade[property]);
								$('#grade_form textarea[name="' + property + '"]').val(grade[property]);
								$('#grade_form input[name="' + property + '"][value="' + grade[property] + '"] ').click();
							}
						}
						$("#grade_form :input").prop("disabled", true);
						$("#grade_modal_close").prop("disabled", false);
						Utils.unblock(selector + " .modal-content");
					},
					function (error) {
						error_text = JSON.parse(error.responseText);
						toastr.error(error_text.msg);
						Utils.unblock(selector + " .modal-content");
					}
				);
			}, 300);
		});
	},

	get_by_id: function (id) {
		var buttonString = "";
		var status = $("#internship_tablist .active").text();
		if (status.charAt(status.length - 1) == 1) status = status.substr(0, status.length - 1);
		status = status.toUpperCase();
		if (
			Internship.user.type.localeCompare("COMPANY_USER") == 0 &&
			(status == "PENDING" || status == "DENIED" || status == "APPROVED")
		) {
			$("#internship_large_modal .modal-body").load("remote_modals/add_internship_modal.html", function () {
				$("#internship_large_modal").modal("toggle");
				setTimeout(function () {
					Utils.block("#internship_large_modal .modal-content", {
						overlayColor: "#000000",
						type: "loader",
						state: "info",
						size: "lg",
					});

					Utils.http_get(
						"rest/internships/" + id,
						function (data) {
							for (var property in data) {
								if (data.hasOwnProperty(property)) {
									$('#add_internship_form input[name="' + property + '"]').val(data[property]);
									$('#add_internship_form textarea[name="' + property + '"]').val(data[property]);
								}
							}
							Internship.department_selection(
								function () {
									$("#departments_dropdown").val(data.department_id);
									$("#departments_dropdown").trigger("change");
									Utils.unblock("#internship_large_modal .modal-content");
								},
								function (error) {
									error_text = JSON.parse(error.responseText);
									toastr.error(error_text.msg);
									Utils.unblock("#internship_large_modal .modal-content");
								}
							);

							buttonString +=
								' <button type ="button" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air btn-danger" onclick="Internship.delete_internship(' +
								data.id +
								')" onclick="">Delete</button> ';

							buttonString +=
								'<button type ="submit" onclick="Internship.update_internship(' +
								data.id +
								')" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air">Update</button> ';

							// buttonString +=
							// 	"<button class='btn btn-outline-focus  m-btn m-btn--pill m-btn--custom' data-dismiss='modal'" +
							// 	">Cancel</button>";

							$("#internship_add_buttons").html(buttonString);
						},
						function (error) {
							error_text = JSON.parse(error.responseText);
							toastr.error(error_text.msg);
							Utils.unblock("#internship_large_modal .modal-content");
						}
					);
				}, 300);
			});
		} else {
			$("#internship_small_modal .modal-body").load("remote_modals/internship_info_modal.html", function () {
				$("#internship_small_modal").modal("toggle");
				setTimeout(function () {
					Utils.block("#internship_small_modal .modal-content", {
						overlayColor: "#000000",
						type: "loader",
						state: "info",
						size: "lg",
					});
					Utils.http_get(
						"rest/internships/" + id,
						function (data) {
							if (Internship.user.type.localeCompare("INTERN") == 0 && status == "AVALILABLE") {
								$("#select_document").removeAttr("hidden");
								Interns.document_selection();
								buttonString +=
									' <button type="button" class="btn btn-outline-focus  m-btn m-btn--pill m-btn--custom m-btn--air" data-dismiss="modal">Cancel</button>';
								buttonString +=
									' <button type="button" id="interntshipApplyButton" onclick="Internship.apply(' +
									data.id +
									')" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air btn-info">Apply</button>';
							} else if (Internship.user.type.localeCompare("INTERN") == 0 && status != "AVALILABLE") {
								$("#select_document").attr("hidden", true);
								buttonString +=
									' <button type="button" class="btn btn-outline-focus  m-btn m-btn--pill m-btn--custom m-btn--air" data-dismiss="modal">Cancel</button>';
							} else {
								$("#select_document").attr("hidden", true);
							}
							$("#modal_job_description").text(data.job_description);
							$("#modal_title").text(data.title);
							$("#modal_department").text(data.department_name);
							if (Internship.user.type.localeCompare("PROFESSOR") == 0 && data.status == "PENDING") {
								buttonString +=
									' <button type="button" id="interntshipDenyButton" onclick="Internship.update_status(' +
									data.id +
									", 'DENIED')\" class='btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air btn-danger'>Deny</button> ";
								buttonString +=
									'<button type="button"id="interntshipApproveButton" onclick="Internship.update_status(' +
									data.id +
									", 'APPROVED')\" class='btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air'>Approve</button>";
							} else if (
								Internship.user.type.localeCompare("PROFESSOR") == 0 &&
								data.status.localeCompare("PENDING") != 0
							) {
								buttonString +=
									' <button type="button" class="btn btn-outline-focus  m-btn m-btn--pill m-btn--custom m-btn--air" data-dismiss="modal">Cancel</button>';
							} else if (
								Internship.user.type.localeCompare("COMPANY_USER") == 0 &&
								data.status.localeCompare("ACTIVE") == 0
							) {
								buttonString +=
									' <button type="button" class="btn btn-outline-focus  m-btn m-btn--pill m-btn--custom m-btn--air" data-dismiss="modal">Cancel</button>';

								buttonString +=
									' <button  id="interntshipGradeButton" type="button" onclick="Internship.post_grade(' +
									data.id +
									"," +
									data.intern_id +
									')" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air btn-info" >Grade</button>';
							} else if (
								Internship.user.type.localeCompare("COMPANY_USER") == 0 &&
								data.status.localeCompare("ACTIVE") != 0
							) {
								buttonString +=
									' <button type="button" class="btn btn-outline-focus  m-btn m-btn--pill m-btn--custom m-btn--air" data-dismiss="modal">Cancel</button>';
							}
							$("#internship_more_buttons").html(buttonString);
							Utils.unblock("#internship_small_modal .modal-content");
						},
						function (error) {
							error_text = JSON.parse(error.responseText);
							toastr.error(error_text.msg);
							Utils.unblock("#internship_small_modal .modal-content");
						}
					);
				}, 300);
			});
		}
	},

	post_grade: function (id, intern_id) {
		$("#internship_large_modal .modal-body").load("remote_modals/grade_modal.html", function () {
			$("#grade_start_date").datepicker({
				format: "yyyy-mm-dd",
				autoclose: true,
			});

			$("#grade_end_date").datepicker({
				format: "yyyy-mm-dd",
				autoclose: true,
			});
			$("#internship_large_modal").modal("toggle");
			$("#grade_modal_button").attr("onclick", "Company.grade_internship(" + id + ", " + intern_id + ")");
			Company.validate_grade();
		});
	},

	get_mentor_id: function (selector, id) {
		setTimeout(function () {
			Utils.block(selector + " .modal-content", {
				overlayColor: "#000000",
				type: "loader",
				state: "info",
				size: "lg",
			});
			Utils.http_get(
				"rest/companies/" + id,
				function (data) {
					for (var property in data) {
						if (data.hasOwnProperty(property)) {
							$("#mentor_modal_" + property + "").text(data[property]);
						}
					}
					Utils.unblock(selector + " .modal-content");
				},
				function (error) {
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
					Utils.unblock(selector + " .modal-content");
				}
			);
		}, 300);
	},

	insert: function (selector) {
		Internship.validate(selector, function () {
			Utils.block("#internship_large_modal .modal-content", {
				overlayColor: "#000000",
				type: "loader",
				state: "dark",
				size: "lg",
			});
			Utils.http_post(
				"rest/internships",
				selector,
				function () {
					toastr.success("Successfully added internship, waiting for verification from staff");
					Utils.unblock("#internship_large_modal .modal-content");
					$("#internship_large_modal").modal("hide");
					table = $("#internship_table").DataTable();
					table.clear();
					table.draw();
				},
				null,
				function (error) {
					Utils.unblock("#internship_large_modal .modal-content");
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
				}
			);
		});
	},

	validate: function (selector, callBack) {
		$(selector).validate({
			rules: {
				title: {
					required: true,
					minlength: 5,
					maxlength: 50,
				},

				start_date: {
					required: true,
				},

				end_date: {
					required: true,
				},
				job_description: {
					required: true,
					minlength: 5,
					maxlength: 1000,
				},
			},

			messages: {
				title: {
					minlength: "Your title must be at least 5 characters long",
					maxlength: "Your title must be less than 50 characters long",
				},

				job_description: {
					minlength: "The job description must be longer than 5",
					maxlength: "The job description must be shorter than 1000",
				},
			},

			submitHandler: function (form, event) {
				event.preventDefault();
				callBack();
			},
		});
	},

	department_selection: function (success_callback, error_callback) {
		Utils.http_get(
			"rest/departments/",
			function (data) {
				var html = "";
				for (var i = 0; i < data.length; i++) html += "<option value=" + data[i].id + ">" + data[i].name + "</option>";
				$("#departments_dropdown").html(html);
				$("#departments_dropdown").select2({
					placeholder: "Select a option",
				});
				success_callback();
			},
			function (error) {
				if (error_callback) {
					error_callback(error);
				} else {
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
				}
			}
		);
	},

	tablist_selection: function (tabs, company_id) {
		var tabString = "";
		var queue = [];
		for (var i = 0; i < tabs.length; i++) {
			if (tabs[i].localeCompare("AVAILABLE") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusAvailable" class="nav-link approved-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Internship.get_all('#internship_table', 'APPROVED', " +
					company_id +
					');">Avalilable</a></li>';
				queue.push("statusAvailable");
			} else if (tabs[i].localeCompare("PENDING") == 0) {
				tabString +=
					"<li class='nav-item'><a id =\"statusPending\"  class='nav-link pending-tab' href='' data-toggle='tab' name='status'" +
					"onclick=\"Internship.get_all('#internship_table', 'PENDING', " +
					company_id +
					');">Pending</a></li>';
				queue.push("statusPending");
			} else if (tabs[i].localeCompare("APPROVED") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusApproved"  class="nav-link approved-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Internship.get_all('#internship_table', 'APPROVED', " +
					company_id +
					');">Approved</a></li>';
				queue.push("statusApproved");
			} else if (tabs[i].localeCompare("ACTIVE") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusActive"  class="nav-link active-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Internship.get_all('#internship_table', 'ACTIVE', " +
					company_id +
					');">Active</a></li>';
				queue.push("statusActive");
			} else if (tabs[i].localeCompare("COMPLETED") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusCompleted"  class="nav-link completed-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Internship.get_all('#internship_table', 'COMPLETED', " +
					company_id +
					');">Completed</a> </li>';
				queue.push("statusCompleted");
			} else if (tabs[i].localeCompare("DENIED") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusDenied"  class="nav-link denied-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Internship.get_all('#internship_table', 'DENIED', " +
					company_id +
					');">Denied</a> </li>';
				queue.push("statusDenied");
			}
		}

		$("#internship_tablist").html(tabString);
		$("#" + queue.shift()).addClass("active");
	},

	apply: function (id) {
		Utils.block("#internship_small_modal .modal-content", {
			overlayColor: "#000000",
			type: "loader",
			state: "info",
			size: "lg",
		});
		Utils.http_post(
			"rest/intern/apply",
			null,
			function () {
				toastr.success("Successfully applied, waiting for response from the company");
				Utils.unblock("#internship_small_modal .modal-content");
				$("#internship_small_modal").modal("toggle");
			},
			{
				internship_id: id,
				intern_id: Internship.user.id,
				status: "PENDING",
				documents_id: JSON.stringify({ id_array: $("#documents_dropdown").val() }),
			},
			function (error) {
				error_text = JSON.parse(error.responseText);
				toastr.error(error_text.msg);
				Utils.unblock("#internship_small_modal .modal-content");
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

		if (Internship.user.type.localeCompare("PROFESSOR") == 0) {
			Professor.internship_tablist_selection(
				["PENDING", "APPROVED", "ACTIVE", "COMPLETED", "DENIED"],
				Internship.user.department_id
			);
			Internship.get_all("#internship_table", "PENDING", null, Internship.user.department_id);
		} else if (Internship.user.type.localeCompare("COMPANY_USER") == 0) {
			$("#addInternshipButton").removeAttr("hidden");
			Internship.tablist_selection(
				["APPROVED", "PENDING", "ACTIVE", "COMPLETED", "DENIED"],
				Internship.user.company_id
			);
			Internship.get_all("#internship_table", "APPROVED", Internship.user.company_id);
		} else if (Internship.user.type.localeCompare("INTERN") == 0) {
			Interns.tablist_selection(
				["AVAILABLE", "PENDING", "ACTIVE", "COMPLETED", "DENIED"],
				Internship.user.department_id
			);
			Internship.get_all("#internship_table", "APPROVED", null, Internship.user.department_id);
			Interns.document_selection();
		}
	},
};

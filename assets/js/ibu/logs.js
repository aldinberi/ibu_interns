var Logs = {
	user: Utils.parseJwt(localStorage.getItem("userToken")),
	timepicker: null,

	add_timepicker: function (time = '00:00') {
		var options = {
			now: time,
			twentyFour: true,
			upArrow: 'wickedpicker__controls__control-up',
			downArrow: 'wickedpicker__controls__control-down',
			close: 'wickedpicker__close',
			hoverState: 'hover-state',
			title: 'Select the duration of work',
			showSeconds: false,
			secondsInterval: 1,
			minutesInterval: 1,
			beforeShow: null,
			show: null,
			clearable: false,
		};
		Logs.timepicker = $('.timepicker').wickedpicker(options);
	},

	add: function () {
		$("#log_large_modal .modal-body").load("remote_modals/add_log_modal.html", function () {
			$("#log_large_modal").modal("toggle");
			$("#log_date").datepicker({
				format: "yyyy-mm-dd",
				autoclose: true,
			});
			Logs.add_timepicker();
			var buttonStr = "";
			buttonStr +=
				'<button class="btn btn-outline-focus  m-btn m-btn--pill m-btn--custom" data-dismiss="modal">Cancel</button> ';
			buttonStr +=
				'<button type="submit" onclick="Logs.insert();" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air">Save</button> ';
			$("#logs_add_buttons").html(buttonStr);
			setTimeout(function () {
				Utils.block("#log_large_modal .modal-content", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				Internship.dropdown(
					"rest/users/intern/" + Logs.user.id + "/internships?status1=APPROVED",
					"#log_modal_dropdown",
					function () {
						Utils.unblock("#log_large_modal .modal-content");
					},
					function (error) {
						error_text = JSON.parse(error.responseText);
						toastr.error(error_text.msg);
						Utils.unblock("#log_large_modal .modal-content");
					}
				);
			}, 300);
		});
	},

	tablist_selection: function (tabs) {
		var tabString = "";
		var queue = [];
		for (var i = 0; i < tabs.length; i++) {
			if (tabs[i].localeCompare("PENDING") == 0) {
				tabString +=
					"<li class='nav-item'><a id ='statusPending' class='nav-link pending-tab' href='' data-toggle='tab' name='status'" +
					"onclick=\"Logs.get_all('PENDING', $('#log_dropdown option:selected').val())\">Pending</a></li>";
				queue.push("statusPending");
			} else if (tabs[i].localeCompare("APPROVED") == 0) {
				tabString +=
					"<li class='nav-item'><a id ='statusApproved' class='nav-link approved-tab' href='' data-toggle='tab' name='status'" +
					"onclick=\"Logs.get_all('APPROVED', $('#log_dropdown option:selected').val())\">Approved</a></li>";
				queue.push("statusApproved");
			} else if (tabs[i].localeCompare("DENIED") == 0) {
				tabString +=
					"<li class='nav-item'><a id ='statusDenied' class='nav-link denied-tab' href='' data-toggle='tab' name='status'" +
					"onclick=\"Logs.get_all('DENIED', $('#log_dropdown option:selected').val());\">Denied</a> </li>";
				queue.push("statusDenied");
			}
		}

		$("#log_tablist").html(tabString);
		$("#" + queue.shift()).addClass("active");
	},

	get_all: function (status, internship_id = null, intern_id = null) {
		if (status != null) {
			if (status.charAt(status.length - 1) == 1) status = status.substr(0, status.length - 1);
			status = status.toUpperCase();
		}
		if (jQuery.active == 0) {
			var table;
			if (!internship_id && Logs.user.type.localeCompare("PROFESSOR") != 0) {
				var internship_id = $("#log_dropdown option:selected").val();
			}

			if (!intern_id && Logs.user.type.localeCompare("PROFESSOR") == 0) {
				var intern_id = $("#log_dropdown option:selected").val();
			}

			if (status != null && (internship_id || intern_id)) {
				$('a[name="status"]').attr("style", "pointer-events: none; cursor: default");

				var url = "rest/logs/datatables?status=" + status;

				if (internship_id) {
					url += "&internship_id=" + internship_id;
				}

				if (intern_id) {
					url += "&intern_id=" + intern_id;
				}

				$.fn.dataTable.ext.errMode = "none";

				$("#logs_table").DataTable().clear().destroy();
				table = $("#logs_table").DataTable({
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
							toastr.error("Error occured: " + xhr.responseJSON.msg);
						},
					},
					columns: [{ data: "log_id" }, { data: "date" }, { data: "status" }, { data: "id" }],
					columnDefs: [
						{
							targets: 2,
							render: function (data, type, full, meta) {
								if (data == "PENDING") {
									return '<span class="m-badge m-badge--warning m-badge--wide">PENDING</span>';
								} else if (data == "APPROVED") {
									return '<span class="m-badge m-badge--info m-badge--wide">APPROVED</span>';
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
									'<a class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air" onclick="Logs.get_by_id(' +
									data +
									')"> <span> <i class="la la-plus"></i> <span>Work done</span></span></a>'
								);
							},
						},
					],
				});
				table.one("xhr", function (e, settings, json) {
					$('a[name="status"]').removeAttr("style");
				});
			} else {
				table = $("#logs_table").DataTable();
			}

			table.column(0).visible(false);
		}
	},

	get_by_id: function (id) {
		var buttonString = "";
		var status = $("#log_tablist .active").text();
		if (status.charAt(status.length - 1) == 1) status = status.substr(0, status.length - 1);
		status = status.toUpperCase();
		if (Internship.user.type == "INTERN" && (status == "PENDING" || status == "DENIED")) {
			$("#log_large_modal .modal-body").load("remote_modals/add_log_modal.html", function () {
				$("#log_large_modal").modal("toggle");
				$("#log_date").datepicker({
					format: "yyyy-mm-dd",
					autoclose: true,
				});

				setTimeout(function () {
					Utils.block("#log_large_modal .modal-content", {
						overlayColor: "#000000",
						type: "loader",
						state: "info",
						size: "lg",
					});
					Utils.http_get(
						"rest/logs/" + id,
						function (data) {
							for (var property in data) {
								if (data.hasOwnProperty(property)) {
									$('#add_logs_form input[name="' + property + '"]').val(data[property]);
									$('#add_logs_form textarea[name="' + property + '"]').val(data[property]);
								}
							}
							$("#log_date").val(data.date);
							$("#log_time").val(data.time);
							Logs.add_timepicker(data.time);
							buttonString +=
								' <button type ="button" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air btn-danger" onclick="Logs.delete_log(' +
								+data.log_id +
								')" onclick="">Delete</button> ';

							buttonString +=
								'<button type ="submit" onclick="Logs.update_log(' +
								data.log_id +
								')" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air">Update</button> ';

							$("#logs_add_buttons").html(buttonString);
							Internship.dropdown(
								"rest/users/intern/" + Logs.user.id + "/internships?status1=APPROVED",
								"#log_modal_dropdown",
								function () {
									$("#log_modal_dropdown").val(data.internship_id);
									$("#log_modal_dropdown").trigger("change");
									Utils.unblock("#log_large_modal .modal-content");
								},
								function (error) {
									error_text = JSON.parse(error.responseText);
									toastr.error(error_text.msg);
									Utils.unblock("#log_large_modal .modal-content");
								}
							);
						},
						function (error) {
							error_text = JSON.parse(error.responseText);
							toastr.error(error_text.msg);
							Utils.unblock("#log_large_modal .modal-content");
						}
					);
				}, 300);
			});
		} else {
			$("#log_small_modal .modal-body").load("remote_modals/log_info_modal.html", function () {
				$("#log_small_modal").modal("toggle");
				setTimeout(function () {
					Utils.block("#log_small_modal .modal-content", {
						overlayColor: "#000000",
						type: "loader",
						state: "info",
						size: "lg",
					});
					Utils.http_get(
						"rest/logs/" + id,
						function (data) {
							$("#modal_work_done").text(data.work_done);
							$("#modal_work_date").text(data.date);
							$("#modal_work_time").text(data.time);
							if (Logs.user.type == "COMPANY_USER" && data.status != "APPROVED" && data.status != "DENIED") {
								buttonString +=
									' <button type="button" id="logDenyButton" onclick="Logs.update_status(' +
									data.log_id +
									", 'DENIED')\" class='btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air btn-danger'>Deny</button> ";

								buttonString +=
									'<button type="button"id="logApproveButton" onclick="Logs.update_status(' +
									data.log_id +
									", 'APPROVED')\" class='btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air'>Approve</button> ";
							} else {
								buttonString +=
									"<button class='btn btn-outline-focus  m-btn m-btn--pill m-btn--custom' data-dismiss='modal'" +
									">Cancel</button> ";
							}

							$("#logs_more_buttons").html(buttonString);
							Utils.unblock("#log_small_modal .modal-content");
						},
						function (error) {
							error_text = JSON.parse(error.responseText);
							toastr.error(error_text.msg);
							Utils.unblock("#log_small_modal .modal-content");
						}
					);
				}, 300);
			});
		}
	},

	update_log: function (id) {
		Logs.validate(function () {
			var data = {
				work_done: $("#work_done").val(),
				date: $("#log_date").val(),
				time: $("#log_time").val()
			};
			setTimeout(function () {
				Utils.block("#log_large_modal .modal-content", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				Utils.http_put(
					"rest/logs/" + id,
					null,
					function () {
						table = $("#logs_table").DataTable();
						table.clear();
						table.draw();
						toastr.success("Successfully updated log, waiting for verification from the company");
						Utils.unblock("#log_large_modal .modal-content");
						$("#log_large_modal").modal("toggle");
					},
					data,
					function (error) {
						error_text = JSON.parse(error.responseText);
						toastr.error(error_text.msg);
						Utils.unblock("#log_large_modal .modal-content");
					}
				);
			}, 300);
		});
	},

	delete_log: function (id) {
		setTimeout(function () {
			Utils.block("#log_large_modal .modal-content", {
				overlayColor: "#000000",
				type: "loader",
				state: "info",
				size: "lg",
			});
			Utils.http_delete(
				"rest/logs/" + id,
				function () {
					var table = $("#logs_table").DataTable();
					table.clear();
					table.draw();
					toastr.success("Successfully deleted log");
					Utils.unblock("#log_large_modal .modal-content");
					$("#log_large_modal").modal("toggle");
				},
				function (error) {
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
					Utils.unblock("#log_large_modal .modal-content");
				}
			);
		}, 300);
	},

	update_status: function (id, status) {
		$("button").click(function (event) {
			event.preventDefault();
		});
		Utils.block("#log_small_modal .modal-content", {
			overlayColor: "#000000",
			type: "loader",
			state: "info",
			size: "lg",
		});
		Utils.http_put(
			"rest/logs/" + id + "/status",
			null,
			function () {
				var table = $("#logs_table").DataTable();
				table.clear();
				table.draw();
				toastr.success("Successfully updated log status");
				Utils.unblock("#log_small_modal .modal-content");
				$("#log_small_modal").modal("toggle");
			},
			{ status: status },
			function (error) {
				error_text = JSON.parse(error.responseText);
				toastr.error(error_text.msg);
				Utils.unblock("#log_small_modal .modal-content");
			}
		);
	},

	insert: function () {
		Logs.validate(function () {
			var data = {
				internship_id: $("#log_modal_dropdown option:selected").val(),
				work_done: $("#work_done").val(),
				date: $("#log_date").val(),
				time: Logs.timepicker.wickedpicker('time')
			};
			console.log(data);
			setTimeout(function () {
				Utils.block("#log_large_modal .modal-content", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				Utils.http_post(
					"rest/logs",
					null,
					function () {
						toastr.success("Successfully added log, waiting for response from the company");
						var table = $("#logs_table").DataTable();
						table.clear();
						table.draw();
						Utils.unblock("#log_large_modal .modal-content");
						$("#log_large_modal").modal("hide");
					},
					data,
					function (error) {
						error_text = JSON.parse(error.responseText);
						if (error_text.msg) {
							toastr.error(error_text.msg);
						} else {
							if (error_text.error_message == "Field `internship_id` is required.") {
								toastr.error("An internship must be selected to post a log");
							} else {
								toastr.error(error_text.error_message);
							}
						}

						Utils.unblock("#log_large_modal .modal-content");
					}
				);
			}, 300);
		});
	},

	validate: function (callBack) {
		$("#add_logs_form").validate({
			rules: {
				work_done: {
					required: true,
					minlength: 5,
					maxlength: 1000,
				},

				log_date: {
					required: true,
				},
			},
			messages: {
				work_done: {
					minlength: "Your title must be at least 5 characters long",
					maxlength: "Your title must be less than 1000 characters long",
				},
			},

			submitHandler: function (form, event) {
				event.preventDefault();
				callBack();
			},
		});
	},

	dropdown: function (url, selector, callBack, errorBack) {
		Utils.http_get(
			url,
			function (data) {
				var options = "<option></option>";
				for (i = 0; i < data.length; i++) {
					options += "<option value= '" + data[i].id + "'>" + data[i].name + "</option>";
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

	options: function () {
		var buttonString = "";
		if (Logs.user.type.localeCompare("COMPANY_USER") == 0) {
			buttonString +=
				'<button type="button" id="logApproveButton" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air">Approve</button>';
			buttonString +=
				' <button type="button" id="logDenyButton" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air btn-danger">Deny</button>';
		}
		buttonString +=
			' <button class="btn btn-outline-focus  m-btn m-btn--pill m-btn--custom m-btn--air" data-dismiss="modal" >Cancel</button>';
		$("#logs_more_buttons").html(buttonString);
	},

	init: function () {
		$("body").on("click", '[data-toggle="modal"]', function () {
			$($(this).data("target") + " .modal-body").load($(this).data("remote"));
		});
		$("body").on("hidden.bs.modal", ".modal", function () {
			$(this).find(".modal-body").empty();
		});
		Logs.get_all(null, null);
		if (Logs.user.type.localeCompare("INTERN") == 0) {
			$("#addLogsButton").removeAttr("hidden");
			Internship.dropdown(
				"rest/users/intern/" + Logs.user.id + "/internships?status1=APPROVED&status2=COMPLETED",
				"#log_dropdown",
				function () {
					Logs.get_all("APPROVED");
					Logs.tablist_selection(["APPROVED", "PENDING", "DENIED"]);
					$("#log_dropdown").attr(
						"onchange",
						"Logs.get_all($('#log_tablist .active').text(), $('#log_dropdown option:selected').val())"
					);
				}
			);
		} else if (Logs.user.type.localeCompare("COMPANY_USER") == 0) {
			Internship.dropdown(
				"rest/internships?company_id=" + Logs.user.company_id + "&status=ACTIVE",
				"#log_dropdown",
				function () {
					Logs.tablist_selection(["PENDING", "APPROVED", "DENIED"]);
					$("#log_dropdown").attr(
						"onchange",
						"Logs.get_all($('#log_tablist .active').text(), $('#log_dropdown option:selected').val())"
					);
				}
			);
		} else if (Logs.user.type.localeCompare("PROFESSOR") == 0) {
			Logs.dropdown("rest/users/interns", "#log_dropdown", function () {
				Professor.log_tablist_selection(["PENDING", "APPROVED", "DENIED"]);
				$("#log_dropdown").attr(
					"onchange",
					"Logs.get_all($('#log_tablist .active').text(), null, $('#log_dropdown option:selected').val())"
				);
			});
		}
		Logs.options();
	},
};

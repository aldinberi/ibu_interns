var Professor = {
	internship_tablist_selection: function (tabs, department_id) {
		var tabString = "";
		var queue = [];
		for (var i = 0; i < tabs.length; i++) {
			if (tabs[i].localeCompare("AVAILABLE") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusAvailable" class="nav-link approved-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Internship.get_all('#internship_table', 'APPROVED', nuul, " +
					department_id +
					');">Avalilable</a></li>';
				queue.push("statusAvailable");
			} else if (tabs[i].localeCompare("PENDING") == 0) {
				tabString +=
					"<li class='nav-item'><a id =\"statusPending\" class='nav-link pending-tab' href='' data-toggle='tab' name='status'" +
					"onclick=\"Internship.get_all('#internship_table', 'PENDING', null, " +
					department_id +
					');">Pending</a></li>';
				queue.push("statusPending");
			} else if (tabs[i].localeCompare("APPROVED") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusApproved" class="nav-link approved-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Internship.get_all('#internship_table', 'APPROVED', null," +
					department_id +
					');">Approved</a></li>';
				queue.push("statusApproved");
			} else if (tabs[i].localeCompare("ACTIVE") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusActive" style="color: #34bfa3" class="nav-link active-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Internship.get_all('#internship_table', 'ACTIVE', null, " +
					department_id +
					');">Active</a></li>';
				queue.push("statusActive");
			} else if (tabs[i].localeCompare("COMPLETED") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusCompleted" class="nav-link completed-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Internship.get_all('#internship_table', 'COMPLETED', null, " +
					department_id +
					');">Completed</a> </li>';
				queue.push("statusCompleted");
			} else if (tabs[i].localeCompare("DENIED") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusDenied" class="nav-link denied-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Internship.get_all('#internship_table', 'DENIED', null, " +
					department_id +
					');">Denied</a> </li>';
				queue.push("statusDenied");
			}
		}

		$("#internship_tablist").html(tabString);
		$("#" + queue.shift()).addClass("active");
	},
	student_tablist_selection: function (tabs) {
		var tabString = "";
		var queue = [];
		for (var i = 0; i < tabs.length; i++) {
			if (tabs[i].localeCompare("ACTIVE") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusActive" style="color: #34bfa3" class="nav-link active-tab" href="" data-toggle="tab" name="status"' +
					'onclick=\'Professor.get_all_students("#students_table", "APPROVED")\'>Active</a></li>';
				queue.push("statusActive");
			} else if (tabs[i].localeCompare("COMPLETED") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusCompleted" class="nav-link completed-tab" href="" data-toggle="tab" name="status"' +
					'onclick=\'Professor.get_all_students("#students_table", "COMPLETED")\'>Completed</a> </li>';
				queue.push("statusCompleted");
			}
		}

		$("#student_tablist").html(tabString);
		$("#" + queue.shift()).addClass("active");
	},

	get_all_students: function (selector, status) {
		if (jQuery.active == 0) {
			$('a[name="status"]').attr("style", "pointer-events: none; cursor: default");

			var url = "rest/users/datatables?";

			if (status) {
				url += "&status=" + status;
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
					{ data: "name" },
					{ data: "year" },
					{ data: "department" },
					{ data: "internship_id" },
					{ data: "company_id" },
					{ data: "internship_id" },
				],
				columnDefs: [
					{
						targets: 4,
						render: function (data, type, full, meta) {
							if (data) {
								return (
									'<button onclick="Professor.get_student_internship(' +
									data +
									')"class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air"> <span> <i class="la la-plus"></i> <span>More</span></span></button>'
								);
							} else {
								return '<span class="m-badge m-badge--metal m-badge--wide">NO INTERNSHIP AVALIABLE</span>';
							}
						},
					},
					{
						targets: 5,
						render: function (data, type, full, meta) {
							return (
								'<button type="button" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air" data-toggle="modal" data-remote="remote_modals/mentor_modal.html" data-target="#student_small_modal" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air"  onclick="Internship.get_mentor_id(' +
								"'#student_small_modal', " +
								data +
								')"> <span> <i class="la la-plus"></i> <span>More</span></span></button>'
							);
						},
					},
					{
						targets: 6,
						render: function (data, type, full, meta) {
							if (data) {
								return (
									"<button onclick=\"Internship.get_grade('#student_large_modal', " +
									data +
									')"class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air"> <span> <i class="la la-plus"></i> <span>More</span></span></button>'
								);
							} else {
								return '<span class="m-badge m-badge--metal m-badge--wide">NO GRADE AVALIABLE</span>';
							}
						},
					},
				],
			});

			if (status != "COMPLETED") {
				table.column(6).visible(false);
			}

			table.column(0).visible(false);

			table.one("xhr", function (e, settings, json) {
				$('a[name="status"]').removeAttr("style");
			});
		}
	},

	log_tablist_selection: function (tabs) {
		var tabString = "";
		var queue = [];
		for (var i = 0; i < tabs.length; i++) {
			if (tabs[i].localeCompare("PENDING") == 0) {
				tabString +=
					"<li class='nav-item'><a id ='statusPending' class='nav-link pending-tab' href='' data-toggle='tab' name='status'" +
					"onclick=\"Logs.get_all('PENDING', null, $('#log_dropdown option:selected').val())\">Pending</a></li>";
				queue.push("statusPending");
			} else if (tabs[i].localeCompare("APPROVED") == 0) {
				tabString +=
					"<li class='nav-item'><a id ='statusApproved' class='nav-link approved-tab' href='' data-toggle='tab' name='status'" +
					"onclick=\"Logs.get_all('APPROVED', null, $('#log_dropdown option:selected').val())\">Approved</a></li>";
				queue.push("statusApproved");
			} else if (tabs[i].localeCompare("DENIED") == 0) {
				tabString +=
					"<li class='nav-item'><a id ='statusDenied' class='nav-link denied-tab' href='' data-toggle='tab' name='status'" +
					"onclick=\"Logs.get_all('DENIED', null, $('#log_dropdown option:selected').val());\">Denied</a> </li>";
				queue.push("statusDenied");
			}
		}

		$("#log_tablist").html(tabString);
		$("#" + queue.shift()).addClass("active");
	},

	get_student_internship: function (id) {
		var buttonString = "";
		$("#student_small_modal .modal-body").load("remote_modals/internship_info_modal.html", function () {
			$("#student_small_modal").modal("toggle");
			setTimeout(function () {
				Utils.block("#student_small_modal .modal-content", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				Utils.http_get(
					"rest/internships/" + id,
					function (data) {
						$("#select_document").attr("hidden", true);
						$("#modal_job_description").text(data.job_description);
						$("#modal_title").text(data.title);
						$("#modal_department").text(data.department_name);
						buttonString +=
							' <button type="button" class="btn btn-outline-focus  m-btn m-btn--pill m-btn--custom m-btn--air" data-dismiss="modal">Cancel</button>';
						$("#internship_more_buttons").html(buttonString);
						Utils.unblock("#student_small_modal .modal-content");
					},
					function (error) {
						error_text = JSON.parse(error.responseText);
						toastr.error(error_text.msg);
						Utils.unblock("#student_small_modal .modal-content");
					}
				);
			}, 300);
		});
	},

	init: function () {
		$("body").on("click", '[data-toggle="modal"]', function () {
			$($(this).data("target") + " .modal-body").load($(this).data("remote"));
		});
		$("body").on("hidden.bs.modal", ".modal", function () {
			$(this).find(".modal-body").empty();
		});
		Professor.get_all_students("#students_table", "APPROVED");
		Professor.student_tablist_selection(["ACTIVE", "COMPLETED"]);
	},
};

var Interns = {
	user: Utils.parseJwt(localStorage.getItem("userToken")),

	init: function () {
		$("body").on("click", '[data-toggle="modal"]', function () {
			$($(this).data("target") + " .modal-body").load($(this).data("remote"));
		});
		$("body").on("hidden.bs.modal", ".modal", function () {
			$(this).find(".modal-body").empty();
		});
		Interns.get_all("#interns_table", null);
		Internship.dropdown(
			"rest/internships?company_id=" + Interns.user.company_id + "&status=APPROVED",
			"#internship_dropdown"
		);
	},

	get_by_id: function (intern_id) {
		$("#interns_small_modal .modal-body").load("remote_modals/intern_info_modal.html", function () {
			var documents_str = "";
			$("#interns_small_modal").modal("toggle");
			setTimeout(function () {
				Utils.block("#interns_small_modal .modal-content", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				$("#interns_more_buttons").removeAttr("hidden");
				var internship_id = $("#internship_dropdown option:selected").val();
				$("#approveButton").attr(
					"onclick",
					"Company.update_intenship_status(" + internship_id + ", " + intern_id + ", 'APPROVED')"
				);
				$("#denyButton").attr(
					"onclick",
					"Company.update_intenship_status(" + internship_id + ", " + intern_id + ", 'DENIED')"
				);

				Utils.http_get(
					"rest/users/intern/" + intern_id + "/internship/" + internship_id,
					function (data) {
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
						Utils.unblock("#interns_small_modal .modal-content");
					},
					function (error) {
						error_text = JSON.parse(error.responseText);
						toastr.error(error_text.msg);
						Utils.unblock("#interns_small_modal .modal-content");
					}
				);
			}, 300);
		});
	},

	download_files: function (files_id) {
		var array_ids = files_id.id_array;
		for (var i = 0; i < array_ids.length; i++) {
			Utils.block("internship_small_modal .modal-content", {
				overlayColor: "#000000",
				type: "loader",
				state: "info",
				size: "lg",
			});
			Utils.http_get(
				"rest/documents/uploads/" + parseInt(array_ids[i]),
				function (data) {
					Utils.download_file(data.document, data.document_name);
					Utils.unblock("internship_small_modal .modal-content");
				},
				function (error) {
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
					Utils.unblock("internship_small_modal .modal-content");
				}
			);
		}
	},

	document_selection: function () {
		Utils.http_get(
			"rest/documents/uploads/intern/" + Interns.user.id,
			function (data) {
				var options = "<option></option>";
				for (i = 0; i < data.length; i++) {
					options += "<option value= '" + data[i].id + "'>" + data[i].document_name + "</option>";
				}
				$("#documents_dropdown").attr("multiple", "multiple");
				$("#documents_dropdown").html(options);
				$("#documents_dropdown").select2({
					placeholder: "Attact a document",
				});
			},
			function (error) {
				error_text = JSON.parse(error.responseText);
				toastr.error(error_text.msg);
			}
		);
	},

	tablist_selection: function (tabs, department_id) {
		var tabString = "";
		var queue = [];
		for (var i = 0; i < tabs.length; i++) {
			if (tabs[i].localeCompare("AVAILABLE") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusAvailable" class="nav-link approved-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Internship.get_all('#internship_table', 'APPROVED', null," +
					department_id +
					');">Avalilable</a></li>';
				queue.push("statusAvailable");
			} else if (tabs[i].localeCompare("PENDING") == 0) {
				tabString +=
					"<li class='nav-item'><a id =\"statusPending\" class='nav-link pending-tab' href='' data-toggle='tab' name='status'" +
					"onclick=\"Internship.get_all('#internship_table', null, null," +
					department_id +
					", " +
					Interns.user.id +
					", 'PENDING');\">Pending</a> </li>";
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
					"onclick=\"Internship.get_all('#internship_table', 'ACTIVE', null," +
					department_id +
					", " +
					Interns.user.id +
					", 'APPROVED');\">Active</a></li>";
				queue.push("statusActive");
			} else if (tabs[i].localeCompare("COMPLETED") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusCompleted" style="color: #c4c5d6" class="nav-link completed-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Internship.get_all('#internship_table', 'COMPLETED', null," +
					department_id +
					", " +
					Interns.user.id +
					", 'COMPLETED');\">Completed</a> </li>";
				queue.push("statusCompleted");
			} else if (tabs[i].localeCompare("DENIED") == 0) {
				tabString +=
					'<li class="nav-item"><a id ="statusDenied" style="color: #c4c5d6" class="nav-link denied-tab" href="" data-toggle="tab" name="status"' +
					"onclick=\"Internship.get_all('#internship_table', null, null," +
					department_id +
					", " +
					Interns.user.id +
					", 'DENIED');\">Denied</a> </li>";
				queue.push("statusDenied");
			}
		}

		$("#internship_tablist").html(tabString);
		$("#" + queue.shift()).addClass("active");
	},

	get_all: function (selector, status) {
		if (jQuery.active == 0) {
			var table;
			if (status != null) {
				var internship_id = $("#internship_dropdown option:selected").val();

				var url = "rest/users/datatables?status=" + status;

				if (internship_id) {
					url += "&internship_id=" + internship_id;
				}

				$.fn.dataTable.ext.errMode = "none";

				$(selector).DataTable().clear().destroy();
				table = $(selector).DataTable({
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
					columns: [{ data: "id" }, { data: "name" }, { data: "id" }],
					columnDefs: [
						{
							targets: 2,
							render: function (data, type, full, meta) {
								return (
									'<a class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air" onclick="Interns.get_by_id(' +
									data +
									')"> <span> <i class="la la-plus"></i> <span>More</span></span></a>'
								);
							},
						},
					],
				});
			} else {
				table = $(selector).DataTable();
			}
			table.column(0).visible(false);
		}
	},
};

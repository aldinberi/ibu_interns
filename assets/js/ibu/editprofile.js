let EditProfile = {
	user: Utils.parseJwt(localStorage.getItem("userToken")),
	get_company: function () {
		setTimeout(function () {
			Utils.block("#company_info", {
				overlayColor: "#000000",
				type: "loader",
				state: "info",
				size: "lg",
			});
		}, 100);
		Utils.http_get(
			"rest/companies/" + parseInt(EditProfile.user.company_id),
			function (data) {
				for (let property in data) {
					if (data.hasOwnProperty(property)) {
						$('#update_company_form input[name="' + property + '"]').val(data[property]);
						$('#update_company_form textarea[name="' + property + '"]').val(data[property]);
					}
				}
				Utils.unblock("#company_info");
			},
			function (error) {
				error_text = JSON.parse(error.responseText);
				toastr.error(error_text.msg);
				Utils.unblock("#company_info");
			}
		);
	},

	init: function () {
		if (EditProfile.user.type.localeCompare("COMPANY_USER") == 0) {
			EditProfile.get_company();
			$("#company_info").removeAttr("hidden");
			$("#user_info").removeAttr("hidden");
		}
		if (EditProfile.user.type.localeCompare("INTERN") == 0) {
			$("#intern_files").removeAttr("hidden");
			EditProfile.upload_file();
			EditProfile.get_uploaded_files(EditProfile.user.id, "CV");
		}
		Utils.block("#user_info", {
			overlayColor: "#000000",
			type: "loader",
			state: "info",
			size: "lg",
		});
		$('#update_user_form input[name="name"]').val(EditProfile.user.name);
		$('#update_user_form input[name="email"]').val(EditProfile.user.email);
		Utils.unblock("#user_info");
		EditProfile.update_password();
		EditProfile.update_company();
		EditProfile.update_user();
		$("body").on("click", '[data-toggle="modal"]', function () {
			$($(this).data("target") + " .modal-body").load($(this).data("remote"));
		});
	},

	upload_file: function () {
		$("#upload_document_form").validate({
			rules: {
				upload_document: {
					required: true,
				},
				type: {
					required: true,
				},
			},

			submitHandler: function (form, event) {
				event.preventDefault();
				Utils.block("#intern_files", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				var fileInput = document.getElementById("upload_document");
				console.log(fileInput);
				var file = fileInput.files[0];
				console.log(file);
				var reader = new FileReader();
				reader.readAsDataURL(file);
				reader.onload = function () {
					console.log(reader.result);
					Utils.http_post(
						"rest/documents/uploads",
						"#upload_document_form",
						function (data) {
							table = $("#files_table").DataTable();
							table.clear();
							table.draw();
							Utils.unblock("#intern_files");
							toastr.success("Uploaded file");
						},
						{
							document: reader.result,
							document_name: file.name,
							file_type: file.type,
							type: $('input[name="type"]:checked').val(),
						},
						function (error) {
							Utils.unblock("#intern_files");
							error_response = JSON.parse(error.responseText);
							toastr.error(error_response.msg);
						}
					);
				};
			},
		});
	},

	get_file_by_id: function (id) {
		Utils.block("#intern_files", {
			overlayColor: "#000000",
			type: "loader",
			state: "info",
			size: "lg",
		});
		Utils.http_get(
			"rest/documents/uploads/" + id,
			function (data) {
				Utils.download_file(data.document, data.document_name);
				Utils.unblock("#intern_files");
			},
			function (error) {
				error_text = JSON.parse(error.responseText);
				toastr.error(error_text.msg);
				Utils.unblock("#intern_files");
			}
		);
	},

	get_uploaded_files: function (intern_id, type) {
		$('a[name="type"]').attr("style", "pointer-events: none; cursor: default");
		$.fn.dataTable.ext.errMode = "none";
		$("#files_table").DataTable().clear().destroy();
		if (intern_id != null && type != null) {
			var table = $("#files_table").DataTable({
				responsive: true,
				processing: true,
				serverSide: true,
				ajax: {
					url: "rest/documents/uploads/datatable?intern_id=" + intern_id + "&type=" + type,
					type: "GET",
					headers: {
						Bearer: localStorage.getItem("userToken"),
					},
					error: function (xhr, ajaxOptions, thrownError) {
						toastr.error("Error occured: " + thrownError);
					},
				},
				columns: [{ data: "id" }, { data: "document_name" }, { data: "type" }, { data: "id" }, { data: "id" }],
				columnDefs: [
					{
						targets: 3,
						render: function (data, type, full, meta) {
							return (
								'<a class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air btn-info" onclick="EditProfile.get_file_by_id(' +
								data +
								')"> <span><span>Download</span></span></a>'
							);
						},
					},
					{
						targets: 4,
						render: function (data, type, full, meta) {
							return (
								'<a class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air btn-danger" onclick="EditProfile.delete_file(' +
								data +
								')"> <span><span>Delete</span></span></a>'
							);
						},
					},
				],
			});

			table.one("xhr", function (e, settings, json) {
				$('a[name="type"]').removeAttr("style");
			});
		}

		table.column(0).visible(false);
		table.column(2).visible(false);
	},

	update_company: function () {
		$("#update_company_form").validate({
			rules: {
				name: {
					required: true,
					minlength: 3,
					maxlength: 50,
				},
				address: {
					required: true,
					minlength: 3,
					maxlength: 50,
				},
				phone: {
					required: true,
					minlength: 9,
					maxlength: 15,
				},

				website: {
					required: true,
					minlength: 5,
					maxlength: 15,
				},

				email: {
					required: true,
					email: true,
				},
			},

			messages: {
				name: {
					minlength: "The company name must be longer than 3",
					maxlength: "The company name must be shorter than 30",
				},

				address: {
					minlength: "The address must be longer than 3",
					maxlength: "The address must be shorter than 50",
				},

				phone: {
					minlength: "The phone must be longer than 9",
					maxlength: "The phone must be shorter than 15",
				},

				website: {
					minlength: "The company website must be longer than 5",
					maxlength: "The company website must be shorter than 15",
				},
			},

			submitHandler: function (form, event) {
				event.preventDefault();
				Utils.block("#company_info", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				Utils.http_put(
					"rest/companies/" + parseInt(EditProfile.user.company_id),
					"#update_company_form",
					function () {
						Utils.unblock("#company_info");
						toastr.success("Successfully updated company info");
					},
					null,
					function (error) {
						Utils.unblock("#company_info");
						error_text = JSON.parse(error.responseText);
						toastr.error(error_text.msg);
					}
				);
			},
		});
	},

	update_user: function () {
		$("#update_user_form").validate({
			rules: {
				name: {
					required: true,
					minlength: 5,
					maxlength: 50,
				},

				email: {
					required: true,
					email: true,
				},
			},

			messages: {
				name: {
					minlength: "The full name must be longer than 5",
					maxlength: "The full name must be shorter than 30",
				},
			},

			submitHandler: function (form, event) {
				event.preventDefault();
				Utils.block("#user_info", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				Utils.http_put(
					"rest/users/" + EditProfile.user.id,
					"#update_user_form",
					function () {
						toastr.success("Successfully updated user info");
						Utils.unblock("#user_info");
					},
					null,
					function (error) {
						error_text = JSON.parse(error.responseText);
						toastr.error(error_text.msg);
						Utils.unblock("#user_info");
					}
				);
			},
		});
	},

	delete_file: function (id) {
		Utils.block("#intern_files", {
			overlayColor: "#000000",
			type: "loader",
			state: "info",
			size: "lg",
		});
		Utils.http_delete(
			"rest/documents/uploads/" + id,
			function () {
				table = $("#files_table").DataTable();
				table.clear();
				table.draw();
				toastr.success("Successfully deleted document");
				Utils.unblock("#intern_files");
			},
			function () {
				error_text = JSON.parse(error.responseText);
				toastr.error(error_text.msg);
				Utils.unblock("#intern_files");
			}
		);
	},

	update_password: function () {
		$("#change_password_form").validate({
			rules: {
				old_password: {
					required: true,
				},
				new_password: {
					required: true,
					minlength: 4,
					maxlength: 10,
				},

				confirm_new_password: {
					required: true,
					equalTo: "#new_password",
				},
			},

			messages: {
				new_password: {
					minlength: "The password must be longer than 4",
					maxlength: "The password must be shorter than 10",
				},

				confirm_new_password: {
					equalTo: "Enter Confirm Password Same as Password",
				},
			},

			submitHandler: function (form) {
				Utils.block("#password_modal .modal-content", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				Utils.http_put(
					"rest/users/password/" + EditProfile.user.id,
					"#change_password_form",
					function () {
						toastr.success("Successfully updated password");
						Utils.unblock("#password_modal .modal-content");
						$("#password_modal").modal("toggle");
					},
					null,
					function (error) {
						error_text = JSON.parse(error.responseText);
						toastr.error(error_text.msg);
						Utils.unblock("#password_modal .modal-content");
					}
				);
			},
		});
	},
};

/* Javascript for admin.php */
let reload_after_download = false;

// start of functions that need to wait on document load
(() => {
	'use strict'
//    console.log('JS init called.')

    // Need functions for both menu's
    // Fetch all menu items we want to apply custom Bootstrap validation styles to
    const menu_items = document.querySelectorAll('.menu-item')
    const canvas_instructions = document.getElementById('canvas-instructions')
    Array.from(menu_items).forEach(menu_item => {
	  menu_item.addEventListener('click', event => {
        let id = event.currentTarget.getAttribute("id");
        switch(id) {
			case "instructions":
                event.preventDefault();
                event.stopPropagation();
                reload_after_download = false;
                (new bootstrap.Offcanvas(canvas_instructions)).show()
				break;
            case "download_sav":
            case "download_sub":
                let file_name = (id == "download_sav") ? 'pmbooking_saved.xlsx' : 'pmbooking_submitted.xlsx';
                event.preventDefault();
                event.stopPropagation();
                $.ajax({
                    url: 'admin_download.php', 
                    type: 'POST',
                    data: {"file_name":file_name, "submitted_only":(id == "download_sub") ? "Y" : "N"},
                    timeout: 3000
                }).done(function(resp) { 
// https://www.geeksforgeeks.org/how-to-trigger-a-file-download-when-clicking-an-html-button-or-javascript/                        
//                        console.log(resp);
                    if (resp.charAt(0) == '{') {
                        let res = JSON.parse(resp);
                        if (res.result == "success") {
                            let element = document.createElement('a');
                            element.setAttribute('href', file_name);
                            element.setAttribute('download', file_name);
                            document.body.appendChild(element);
                            element.click();
                            document.body.removeChild(element);
                            setTimeout(remove_download,45000, file_name);
                            $("#main-body").html("Open file within 30 seconds...");
                            reload_after_download = true;
                        } else{
                            bootbox.alert({ title: "Error", message: res.msg});
                            set_message_1("Download Error");
                            if (res.logged_off) {
                                // Force reload as we are no longer logged on.
                                window.location.replace(SITE_NAME);
                            }
                        }
                    } else {
                        bootbox.alert({ title: "Server Error", message: resp});
                    }
                }).fail(function(e) {
                    set_message_1("Download Error");
                    bootbox.alert({ title: "Error creating booking download file", message: JSON.stringify(e)});
                });
                break;
            case "logout":
                reload_after_download = false;
                event.preventDefault();
                event.stopPropagation();
                bootbox.confirm({ 
                    title: "Logout Confirmation", 
                    message: 'Are you sure you want logout?', 
                    callback: function(result) {
                        if (result) {
                            $.ajax({
                                url: SITE_NAME +"logout.php", 
                                type: 'GET',
                                timeout: 3000
                            }).done(function(result){ 
                                window.location.replace(SITE_NAME);
                            }).fail(function(e) {
                                console.log(JSON.stringify(e));
                            });
                        }
                    }
                })
                break;
			default:
                event.preventDefault();
                event.stopPropagation();
                reload_after_download = false;
                $("#main-body").html('Loading...');
                load_DB_sections(id);
		}
		return false; // Stop page reload.
	  }, false);
    })
    // Load overview by default
    load_DB_sections(OVERVIEW_PREFIX);
})() // end of functions that need to wait on document load

// Load the database data for given prefix
function load_DB_sections(prefix) {
    $.ajax({
        url: 'admin_load.php', 
        type: 'POST',
        data: {"prefix":prefix},
        timeout: 3000
    }).done(function(resp) { 
            let res = JSON.parse(resp);
            if (res.result == "success") {
                $("#main-body").html(res.msg);
                check_load_complete();
            } else{
                bootbox.alert({ title: "Error", message: res.msg});
                set_message_1("Download Error");
                if (res.logged_off) {
                    // Force reload as we are no longer logged on.
                    window.location.replace(SITE_NAME);
                }
            }
    }).fail(function(e) {
        set_message_1("Download Error");
        bootbox.alert({ title: "Error creating loading admin details", message: JSON.stringify(e)});
    });
}

// Remove the xlsx file from server.
function remove_download(file_name) {
    $.ajax({
        url: 'admin_download_remove.php', 
        type: 'POST',
        data: {"file_name":file_name},
        timeout: 3000
    }).done(function(resp) { 
// to-do, do not load this if user naviged to otehr page in the meanwhile
        if (reload_after_download) {
            reload_after_download = false;
            load_DB_sections(OVERVIEW_PREFIX);
        }
    }).fail(function(e) {
        bootbox.alert({ title: "Error removing booking data file ", message: JSON.stringify(e)});
    });
}

// Data sections load complete handlers
function check_load_complete() {
    // Attach event handlers
    var prefix = $("#data_type").val(); 
    switch (prefix) {
        case BOOKING_PREFIX:
            // When clicking on booking, show the booking overview.
            const table_rows = document.querySelectorAll('td');
            Array.from(table_rows).forEach(table_row => {
                table_row.addEventListener("click", (event) => {
                    if (event.target.id) {
                        const canvas_booking_overview = document.getElementById('canvas-booking_overview');
                        var booking_id = event.target.id.match(/\d+/)[0];
                        $("#booking_overview").html('Loading...');
                        booking_generate_overview(booking_id);
                        (new bootstrap.Offcanvas(canvas_booking_overview)).show();
                    }
                });
            });
            break;
    }
    switch (prefix) {
        // Items with select all to handle
        case USERS_PREFIX:
        case BOOKING_PREFIX:
        case PAYMENT_PREFIX:
            const cb_list = document.querySelectorAll("input[type='checkbox'][class=item]"); // Checkboxes except select_all
            Array.from(cb_list).forEach(cb => {
                cb.addEventListener("change", (event) => {
                    // Check /uncheck select all if all cbs are selected
                    if (!event.target.checked) document.getElementById('select_all').checked = false;
                    else {
                        if ([...cb_list].every(cb => cb.checked === true)) document.getElementById('select_all').checked = true;
                    }
                });
            });
            const cb_list_all = document.querySelectorAll(`#select_all`);
            Array.from(cb_list_all).forEach(cb => {
                cb.addEventListener("change", (event) => {
                    cb_list.forEach(function(cb) {
                        cb.checked = event.target.checked;
                    });
                });
            });
            break;
    }
    switch (prefix) {
        // Items with remove button to handle
        case USERS_PREFIX:
        case BOOKING_PREFIX:
        case ROOM_PREFIX:
        case HOTEL_ROOMS_PREFIX:
        case PAYMENT_PREFIX:
        case EXTRA_PREFIX:
            const button_rem = document.querySelectorAll('.btn-inputFormRemove') 
            Array.from(button_rem).forEach(button => {
                button.addEventListener("click", (event) => {
                    $("#submit_action").val("remove");
                    // Get the ID(s) to remove
                    if (prefix == ROOM_PREFIX || prefix == HOTEL_ROOMS_PREFIX || prefix == EXTRA_PREFIX) {
                        $("#selected_ids").val($('input[name="select_id"]:checked').val());
                    } else {
                        ids = [];
                        $("input[name*='select_id']:checked").each(function (i) {
                            ids.push($(this).val());
                        });
                        $("#selected_ids").val(ids.toString());
                    }
                    submit_admin_form(prefix);
                });
            });
            break;
    }
    switch (prefix) {
        // Items with add button to handle
        case PAYMENT_PREFIX:
        case HOTEL_ROOMS_PREFIX:
        case ROOM_PREFIX:
        case EXTRA_PREFIX:
            const button_add = document.querySelectorAll('.btn-inputFormAdd') 
            Array.from(button_add).forEach(button => {
                button.addEventListener("click", (event) => {
                    $("#submit_action").val("add");
                    if (prefix == ROOM_PREFIX || prefix == HOTEL_ROOMS_PREFIX || prefix == EXTRA_PREFIX) {
                        $("#selected_ids").val($('input[name="select_id"]:checked').val());
                    } else {
                        $("#selected_ids").val("");
                    }
                    submit_admin_form(prefix);
                });
            });
            break;
    }
    switch (prefix) {
        // Items with toggle and lock button to handle
        case BOOKING_PREFIX:
            const button_tgl = document.querySelectorAll('.btn-inputFormToggle')
            Array.from(button_tgl).forEach(button => {
                button.addEventListener("click", (event) => {
                    $("#submit_action").val("toggle");
                    // Get the ID(s) to toggle
                    var ids = [];
                    $("input[name*='select_id']:checked").each(function (i) {
                        ids.push($(this).val());
                    });
                    $("#selected_ids").val(ids.toString());
                    submit_admin_form(prefix);
                });
            });
            const button_lck = document.querySelectorAll('.btn-inputFormLock')
            Array.from(button_lck).forEach(button => {
                button.addEventListener("click", (event) => {
                    $("#submit_action").val("lock");
                    submit_admin_form(prefix);
                });
            });
            break;
    }
    switch (prefix) {
        // Items with mail button to handle
        case USERS_PREFIX:
        case BOOKING_PREFIX:
            const button_mail = document.querySelectorAll('.btn-inputFormMail')
            Array.from(button_mail).forEach(button => {
                button.addEventListener("click", (event) => {
                    let btn_id = event.target.id; //event.currentTarget.getAttribute("id");
                    // Email to send is determined by actual button clicked.
                    switch (btn_id) {
                        case 'btn_mail_inactive':
                            reason = 'activate';
                            break;
                        case 'btn_mail_submit':
                            reason = 'submit';
                            break;
                        case 'btn_mail_pay':
                            reason = 'pay';
                            break;
                    }
                    $("#submit_action").val("mail,"+reason);
                    // Get the ID(s) to remove
                    var ids = [];
                    $("input[name*='select_id']:checked").each(function (i) {
                      ids.push($(this).val());
                    });
                    $("#selected_ids").val(ids.toString());
                    submit_admin_form(prefix);
                });
            });
            break;
    }
}

// Submit the form for processing.
function submit_admin_form(prefix) {
    $.ajax({
        url: "admin_submit.php", 
        type: 'POST',
        data: $("#adminform").serialize(), // serializes the form's elements.
        timeout: 3000
    }).done(function(resp) { // display results
        if (resp.charAt(0) == '{') {
            let res = JSON.parse(resp);
            if (res.result == "success") {
                load_DB_sections(prefix);
                bootbox.alert({ title: "Success", message: res.msg});
                set_message_1("");
            } else {
                bootbox.alert({ title: "Error", message: res.msg});
                set_message_1("Submit Error");
                if (res.logged_off) {
                    // Force reload as we are no longer logged on.
                    window.location.replace(SITE_NAME);
                }
            }
        } else {
            bootbox.alert({ title: "Server Error", message: resp});
        }
    }).fail(function(e) {
        set_message_1("Submit Error");
        bootbox.alert({ title: "Error submitting", message: JSON.stringify(e)});
    });

}

// Submit the form for overview generation
function booking_generate_overview(booking_id) {
    $.ajax({
        url: 'booking_overview.php', 
        type: 'POST',
        data: {'booking_id': booking_id, 'isAdminReq': '1'}, 
        timeout: 3000
    }).done(function(resp) { 
        if (resp.charAt(0) == '{') {
            let res = JSON.parse(resp);
            if (res.result == "success") {
                set_message_1("");
                $("#booking_overview").html(res.msg);
            } else {
                bootbox.alert({ title: "Error", message: res.msg});
                set_message_1("Submit Error");
                if (res.logged_off) {
                    // Force reload as we are no longer logged on.
                    window.location.replace(SITE_NAME);
                }
            }
        } else {
            bootbox.alert({ title: "Server Error", message: resp});
        }
    }).fail(function(e) {
        set_message_1("Submit Error");
        bootbox.alert({ title: "Error submitting", message: JSON.stringify(e)});
    });

}

/* Message functions */
function set_message_1(msg) {
    $("#navbar-msg1").html(msg);
}
function set_message_2(msg) {
    $("#navbar-msg2").html(msg);
}


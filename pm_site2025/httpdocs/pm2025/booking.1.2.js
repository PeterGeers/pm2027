/* Javascript for booking.php */
let user_is_alerted = 0;
let prev_club_name = '';

// start of functions that need to wait on document load
(() => {
	'use strict'
//    console.log('JS init called.')

    // Need functions for both menu's
    // Fetch all menu items we want to apply custom Bootstrap validation styles to
    const menu_items = document.querySelectorAll('.menu-item') // Better performance than jquery
    const canvas_instructions = document.getElementById('canvas-instructions')
    const canvas_booking_overview = document.getElementById('canvas-booking_overview')
    Array.from(menu_items).forEach(menu_item => {
	   menu_item.addEventListener('click', event => {
        let id = event.currentTarget.getAttribute("id");
        switch(id) {
			case "instructions":
                event.preventDefault();
                event.stopPropagation();
                $('#instructions_club').html($(`#${CONTACT_PREFIX}0club_name`).val());  // Update clubname in instructions text.
                (new bootstrap.Offcanvas(canvas_instructions)).show()
				break;
            case "overview":
                event.preventDefault();
                event.stopPropagation();
                $("#booking_overview").html('Loading...');
                booking_generate_overview();
                (new bootstrap.Offcanvas(canvas_booking_overview)).show();
				break;
            case "validate":
                event.preventDefault()
                //event.stopPropagation() Don't do this, it will stop validation.
                // warn user
                if ($("#bookingform").get(0).checkValidity()) {
                    set_message_1("Validated");
                    bootbox.alert("Validates succesfull.<br>You can submit your booking.");
                } else {
                    set_message_1("Validate failed");
                    bootbox.alert("Validate failed!!<br>Check for inputs shown in <color:'red';>red</color>.", function() {
//                        event.stopPropagation();
                        $("#bookingform").find(":invalid").first().focus();
//                        console.log("Error in element : ", $("#bookingform").find(":invalid").first().attr('id'));
                    });   
                }
                $("#bookingform").addClass('was-validated');
                break;
            case "save":
                // Trigger submit to save booking data.
                event.preventDefault();
                event.stopPropagation();
                if (booking_complete_locked == 1) {
                    bootbox.alert("It is not possible to save any changes anymore.");
                } else {
                    if ($("#bookingform").data("changed")) {
                        // send form for save
                        booking_save_submit('booking_save.php', 'Saved');
                    } else {
                        bootbox.alert("You did not make any changes.");
                    }
                }
                break;
            case "submit":
                // Trigger submit to submit booking data.
                event.preventDefault();
                if (booking_complete_locked == 1) {
                    bootbox.alert("It is not possible to submit changes anymore.");
                } else {
                    if ($("#bookingform").get(0).checkValidity()) {
                        $("#booking_submitted_count").val(booking_submitted_count);
                        booking_save_submit('booking_submit.php', 'Submitted');
                        event.stopPropagation();
                    } else {
                        set_message_1("Validate failed");
                        bootbox.alert("Validate failed!!<br>Check for inputs shown in <color:'red';>red</color> before submitting.");
                        $("#bookingform").find(":invalid").first().focus();
                    }
                    $("#bookingform").addClass('was-validated');
                }
                break;
			case "logout":
                event.preventDefault();
                event.stopPropagation();
                var msg = "";
				if ($("#bookingform").data("changed")) {
					msg = 'You have made changes to your booking without saving or submitting. <br>Are you sure you want logout?';
				} else {
   					msg = 'Are you sure you want logout?';
				}
                bootbox.confirm({ 
                    title: "Logout Confirmation", 
                    message: msg, 
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
			 alert(id + ' clicked');
		}
		return false; // Stop page reload.
      });
    });
    load_DB_sections();
})() // end of functions that need to wait on document load

// Reset validations if form has changed
function resetValidation() {
    $('#bookingform').removeClass('was-validated');
}
// Set validations when succesfull.
function setValidation() {
    $('#bookingform').addClass('was-validated');
}
// Load data from database and set form functions after load is completed.
function load_DB_sections() {
    $.ajax({
        url: 'booking_load.php', 
        type: 'GET',
        timeout: 3000
    }).done(function(resp) { 
        if (resp.charAt(0) == '{') {
            let res = JSON.parse(resp);
            if (res.result == "success") {
                $("#booking_data").html(res.msg);
                check_load_complete();
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
        bootbox.alert({ title: "Error creating loading booking details", message: JSON.stringify(e)});
    });

}
// Set form sections and events after it has loaded.
function check_load_complete() {
    // Add check if form has changed.
    const form_inputs = document.querySelectorAll('input,textarea,select');
    Array.from(form_inputs).forEach(form_input => {
        form_input.addEventListener("change", () => {
            set_form_changed();
            resetValidation();
            // Update booking reference
            if ($("#booking_submitted_count").val() == booking_submitted_count) {
                booking_submitted_count++;
                $("#booking_ref").val(format_booking_reference())
                $("#navbar-ref").html("Booking Ref # " + $("#booking_ref").val());
            }
        });
    });
    // Catch value changes so we will store the values.
    document.querySelector(`#${CONTACT_PREFIX}0club_name`).addEventListener("change", () => {
        update_club_name();
    });
    update_club_name();

    // Catch value changes so we will store the values.
    document.querySelector(`#${CONTACT_PREFIX}0first_name`).addEventListener("change", () => {
        if ($(`#${CONTACT_PREFIX}0id`).val() == -1) $(`#${CONTACT_PREFIX}0id`).val(0);
    });
    document.querySelector(`#${CONTACT_PREFIX}0last_name`).addEventListener("change", () => {
        if ($(`#${CONTACT_PREFIX}0id`).val() == -1) $(`#${CONTACT_PREFIX}0id`).val(0);
    });
    document.querySelector(`#${CONTACT_PREFIX}0club_name`).addEventListener("change", () => {
        if ($(`#${CONTACT_PREFIX}0id`).val() == -1) $(`#${CONTACT_PREFIX}0id`).val(0);
    });

    // Initialize delegate name change listners
    document.querySelector(`#${DELEGATE_PREFIX}0first_name`).addEventListener("change", () => {
        if ($(`#${DELEGATE_PREFIX}0id`).val() == -1) $(`#${DELEGATE_PREFIX}0id`).val(0);
    });
    document.querySelector(`#${DELEGATE_PREFIX}0last_name`).addEventListener("change", () => {
        if ($(`#${DELEGATE_PREFIX}0id`).val() == -1) {
            $(`#${DELEGATE_PREFIX}0id`).val(0);
        }
    });
    for (id = 0; id < CONST_MAX_DELEGATES; id++) {
        // Set event handlers
        document.querySelector(`#${DELEGATE_PREFIX}${id}first_name`).addEventListener("change", () => { load_room_guest_names_list(); });
        document.querySelector(`#${DELEGATE_PREFIX}${id}last_name`).addEventListener("change", () => { load_room_guest_names_list(); });
    }
    // Initialize guest name change listners
    for (id = 0; id < CONST_MAX_GUESTS; id++) {
        // Set event handlers
        document.querySelector(`#${GUEST_PREFIX}${id}first_name`).addEventListener("change", () => { load_room_guest_names_list(); });
        document.querySelector(`#${GUEST_PREFIX}${id}last_name`).addEventListener("change", () => { load_room_guest_names_list(); });
    }
    // Initialize rooms section for correct show/hide of inputs
    has_rooms = false;
    for (id = 0; id < CONST_MAX_ROOMS; id++) {
        if ($(`#${ROOM_PREFIX}${id}id`).val() != -1) {
            set_room_guest_sections(id);
            has_rooms = true;
        }
        // Set event handlers
        document.querySelector(`#${ROOM_PREFIX}${id}type`).addEventListener("change", (event) => {
            var id = event.target.id.match(/\d+/)[0];
            set_room_guest_sections(id);
        });
        document.querySelector(`#${ROOM_PREFIX}${id}arr_date`).addEventListener("change", () => { set_travel_dates('arr'); });
        document.querySelector(`#${ROOM_PREFIX}${id}dep_date`).addEventListener("change", () => { set_travel_dates('dep'); });
    }
    // Set loaded guest name input validation event for autocomplete.
    load_room_guest_names_list();
    // Initialize travels section for correct show/hide of inputs
    for (id = 0; id < CONST_MAX_TRAVELS; id++) {
        if ($(`#${TRAVEL_PREFIX}${id}id`).val() != -1) {
            set_travel_sections('arr', id);
            set_travel_sections('dep', id);
        }
        // Set event handlers
        document.querySelector(`#${TRAVEL_PREFIX}${id}arr_type`).addEventListener("change", (event) => {
            var id = event.target.id.match(/\d+/)[0];
            set_travel_sections('arr', id);
        });
        document.querySelector(`#${TRAVEL_PREFIX}${id}dep_type`).addEventListener("change", (event) => {
            var id = event.target.id.match(/\d+/)[0];
            set_travel_sections('dep', id);
        });
    }
    const phoneInput = document.querySelector(`#${CONTACT_PREFIX}0phone`);
    window.intlTelInput(phoneInput, {
        countryOrder: OPTION_CC,
        initialCountry: "auto",
        geoIpLookup: callback => {
            fetch("https://ipapi.co/json")
                .then(res => res.json())
                .then(data => callback(data.country_code))
                .catch(() => callback("us"));
        },
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@24.4.0/build/js/utils.js",
    });

    // Attach event click handler to form buttons.
    const button_remove = document.querySelectorAll('.btn-inputFormRemove') // Better performance than jquery
    Array.from(button_remove).forEach(button => {
        button.addEventListener("click", (event) => {
            // Get the id of the clicked button
            let btn_id = event.currentTarget.getAttribute("id");
            var parsedId = parseButtonId('btn_remove_',btn_id);
            if (parsedId) {
                //var text = "Prefix:" + parsedId.prefix + " ID:" + parsedId.id;
                var prefix = parsedId.prefix;
                var id = parseInt(parsedId.id);
                resetValidation();
                set_form_changed();
                add_to_list(`${REMOVED_PREFIX}_${prefix}`, $(`#${prefix}${id}id`).val())
                var fields = DB_FIELDS[prefix];
                var max = MIN_MAX_ITEMS[prefix][1];
                if (id == (max-1) || !is_visible(`${prefix}${id+1}`)) {
                    // Hide/clear the last one shown
                    id_hide = id
                } else {
                    // Copy the data from next delegates up.
                    for (let id_from = id + 1; id_from < max; id_from++) {
                        copy_object_data(prefix, fields, id_from, id_from-1)
                        if (is_visible(`${prefix}${id_from}`)) {id_hide = id_from}
                    }    
                }
                // Hide last shown delegate section.
                show_form_section(`${prefix}${id_hide}`, false);
                clear_object_data(prefix, fields, id_hide);
                switch (prefix) {
                    case DELEGATE_PREFIX:
                        show_button_section(`btn_add_${prefix}${id_hide-1}`,true);
                        if (id_hide==1) {
                            // We cannot remove all delegates, so hide remove button when just one left.
                            show_button_section(`btn_remove_${prefix}0`, false);
                        }
                        load_room_guest_names_list();
                        break;
                    case ROOM_PREFIX:
                        // If we removed last room, show initial add button and hide travel options.
                        if (id_hide==0) {
                            show_button_section(TRAVEL_PREFIX, false);
                        }
                        set_travel_dates('arr');
                        set_travel_dates('dep');
                    case GUEST_PREFIX:
                        load_room_guest_names_list();
                    case TRAVEL_PREFIX:
                        // If we removed last travel, show initial add button
                        if (id_hide==0) {
                            show_button_section(`btn_add_${prefix}`, true);
                        } else {
                            show_button_section(`btn_add_${prefix}${id_hide-1}`, true);
                        }
                        break;
                } 
            } else {
                console.log('btn-inputFormRemove cannot find button id for '+id);
            }
        }, false);
    });
    // btn-inputFormAdd click event handler
    const button_add = document.querySelectorAll('.btn-inputFormAdd') // Better performance than jquery
    Array.from(button_add).forEach(button => {
        button.addEventListener("click", (event) => {
            // Get the id of the clicked button
            let btn_id = event.currentTarget.getAttribute("id");
            var parsedId = parseButtonId('btn_add_',btn_id);
            if (parsedId) {
                var prefix = parsedId.prefix;
                var min = MIN_MAX_ITEMS[prefix][0];
                var id = parseInt(parsedId.id);
                resetValidation();
                set_form_changed();
                show_form_section(`${prefix}${id + 1}`, true);  // can we reduct this to more simple  $(`#${parsedId.prefix}${parsedId.id + 1}`).show();
                show_button_section(`btn_add_${prefix}${id}`, false);
                if (min == 1) {
                    if (id == 0) {
                        // We show more then one of one items, so show button to remove first
                        show_button_section(`btn_remove_${prefix}0`, true);
                    }
                } else {
                    // We show more then one of zero items, so hide button to add first
                    if (id == -1) {
                        show_button_section(`btn_add_${prefix}`, false);
                    }
                    show_button_section(`btn_remove_${prefix}${id}`, true);
                }
                switch (prefix) {
                    case ROOM_PREFIX:
                        // We show more then one room, so show remove button on first. Also show travel to hotel options.
                        if (id == -1) {
                            show_button_section(TRAVEL_PREFIX, true);
                        } 
                        show_input_section(`${prefix}${id+1}guest2Section`, false)
                        show_input_section(`${prefix}${id+1}guest3Section`, false)
                        break;
                    case TRAVEL_PREFIX:
                        show_input_section(`${prefix}${id+1}arr_otherSection`, false)
                        show_input_section(`${prefix}${id+1}arr_planeSection`, false)
                        show_input_section(`${prefix}${id+1}arr_airp_otherSection`, false)
                        show_input_section(`${prefix}${id+1}dep_otherSection`, false)
                        show_input_section(`${prefix}${id+1}dep_planeSection`, false)
                        show_input_section(`${prefix}${id+1}dep_airp_otherSection`, false)
                        break; 
                }
            } else {
                console.log('btn-inputFormAdd cannot find button id for '+id);
            }
        });
    });

    // If we have rooms, show travels section
    if (has_rooms) {
        show_button_section("travels", true);
        set_travel_dates('arr');
        set_travel_dates('dep');
    }
    // When new booking, show instructions.
    if (booking_new == 1) { 
        (new bootstrap.Offcanvas(document.getElementById('canvas-instructions'))).show();
        booking_new = 0;
    }
    // When booking is locked, disable number inputs as this does not seem to work in html and show message is not done so yet in this session.
    if (booking_complete_locked == 1) {
        const imp_list = document.querySelectorAll("input"); // Disable all inputs
        Array.from(imp_list).forEach(imp => {
            imp.disabled = true;
        });
        if (user_is_alerted == 0) {
            bootbox.alert("It is not possible to make any changes to your booking anymore.");
            user_is_alerted = 1;
        }
    } else {
        // Just this booking is partially locked.
        if (booking_locked == 1) {
            const imp_list = document.querySelectorAll("input[type='number']"); // Checkboxes except select_all
            Array.from(imp_list).forEach(imp => {
                imp.disabled = true;
            });
            if (user_is_alerted == 0) {
                bootbox.alert("Your booking is locked.<br>You can only make changes that do not change the booking costs.");
                user_is_alerted = 1;
            }
        }
    }
}

// Get prefix and id out of button ID.
function parseButtonId(pf,buttonId) {
    // Regular expression to match the format btn_<prefix><number>
    //const regex = /^btn_(\w+)(\d+)$/;
    let regex = new RegExp(String.raw`^${pf}(\D+)(\d+)$`);
    const matches = buttonId.match(regex);
    if (matches) {
        // Extracted parts
        const prefix = matches[1];
        const id = matches[2];
        return { prefix: prefix, id: id };
    } else {
        // Try to match w/o number
        regex = new RegExp(String.raw`^${pf}(\D+)$`);
        const matches = buttonId.match(regex);
        if (matches) {
            // Extracted parts
            const prefix = matches[1];
            const id = -1;
            return { prefix: prefix, id: id };
        } else {    
            // Return null if the format doesn't match
            return null;
        }
    }
}

// Format corerct booking reference. Matches the PHP function for server side.
function format_booking_reference() {
    return $("#booking_id").val() + '-' + booking_submitted_count;
}

// If we have predefined clubnames, make sure they are used uniquely for a booking
function update_club_name() {
    if (USE_CLUBS_TABLE == 1) {
        let club_name = $(`#${CONTACT_PREFIX}0club_name`).val();
        let club_country = $(`#${CONTACT_PREFIX}0club_country`).val();
        let booking_id = $("#booking_id").val();
        if (club_name != '' && booking_id != '') {
            if (club_name != OTHER_CLUB_NAME) {
                $.ajax({
                    url: 'booking_check_clubname.php', 
                    type: 'POST',
                    data: {"booking_id":booking_id, "club_name":club_name},
                    timeout: 3000
                }).done(function(resp) { // display results
                    if (resp.charAt(0) == '{') {
                        let res = JSON.parse(resp);
                        if (res.result == "success") {
                            if (res.name == club_name) {
								if (club_country == "") {
                                	$(`#${CONTACT_PREFIX}0club_country`).val(res.cc);
								}
                                $("#page_header_img1_div").html(`<img alt="Club logo" class="fhdce-logo-large animate-width img-fluid" src="club_logos/cl${res.id}.png">`);
                            }
                        } else {
                            $(`#${CONTACT_PREFIX}0club_name`).val(prev_club_name);
                            bootbox.alert({ title: "Club name already used", message: res.msg});
                        }
                        prev_club_name = $(`#${CONTACT_PREFIX}0club_name`).val();
                    } else {
                        bootbox.alert({ title: "System Error", message: resp});
                    }
                }).fail(function(e) {
                    bootbox.alert({ title: "Error validating club name change", message: JSON.stringify(e)});
                });
            } else {
                $("#page_header_img1_div").html("");
//                $(`#${CONTACT_PREFIX}0club_country`).val("");
                prev_club_name = club_name;
            }
        } else {
            prev_club_name = club_name;
        }
    }
}

// Submit the form for processing; Save or Submit
function booking_save_submit(url, success_msg) {
    $.ajax({
        url: url, 
        type: 'POST',
        data: $("#bookingform").serialize(), // serializes the form's elements.
        timeout: 3000
    }).done(function(resp) { // display results
        console.log(resp)
        // We check that we got JSON text rather than PHP error
        if (resp.charAt(0) == '{') {
            let res = JSON.parse(resp);
            if (res.result == "success") {
                // Reload data from DB.
                load_DB_sections();
                // Clear removed sections.
                $(`#${REMOVED_PREFIX}_${CONTACT_PREFIX}`).val("");
                $(`#${REMOVED_PREFIX}_${DELEGATE_PREFIX}`).val("");
                $(`#${REMOVED_PREFIX}_${GUEST_PREFIX}`).val("");
                $(`#${REMOVED_PREFIX}_${ROOM_PREFIX}`).val("");
                $(`#${REMOVED_PREFIX}_${TRAVEL_PREFIX}`).val("");
                $(`#${REMOVED_PREFIX}_${PAYMENT_PREFIX}`).val("");

                bootbox.alert({ title: "Booking Status", message: res.msg});
                $("#bookingform").data("changed",false);
                set_message_1(success_msg);
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
    }).fail (function(e) {
        set_message_1("Submit Error");
        bootbox.alert({ title: "Error submitting booking", message: JSON.stringify(e)});
    });
}

// Submit the form for overview generation
function booking_generate_overview() {
    $.ajax({
        url: 'booking_overview.php', 
        type: 'POST',
        data: $("#bookingform").serialize(), // serializes the form's elements.
        timeout: 3000
    }).done(function(resp) { // display results
        if (resp.charAt(0) == '{') {
            let res = JSON.parse(resp);
            if (res.result == "success") {
                $("#booking_overview").html(res.msg);
                $("#overview_booking_ref").html($("#booking_ref").val());
            } else {
                bootbox.alert({ title: "Error", message: res.msg});
                set_message_1("Submit Error");
                if (res.logged_off) {
                    // Force reload as we are no longer logged on.
                    window.location.replace(SITE_NAME);
                }
            }
        } else {
            bootbox.alert({ title: "System Error", message: resp});
        }
    }).fail(function(e) {
        set_message_1("Submit Error");
        bootbox.alert({ title: "Error submitting booking", message: JSON.stringify(e)});
    });
}

/* Message functions */
function set_form_changed() { // Seperate function as this happens a lot
   set_message_1("Changed");
   $("#bookingform").data("changed",true);
}
function set_message_1(msg) {
    $("#navbar-msg1").html(msg)
}
function set_message_2(msg) {
    $("#navbar-msg2").html(msg)
}
// Add id to remove list if not zero
function add_to_list(list_id, idx) {
    if (idx > 0) {
        var cur_val = $(`#${list_id}`).val()
        if (cur_val.length > 0) { cur_val+=','}
        cur_val+=idx
        $(`#${list_id}`).val(cur_val)
    }    
}
// Show the travel section depending on type.
function set_travel_sections(dir, id) {
//    console.log(`Travel ${dir} type ${id} changed to ` + $(`#${TRAVEL_PREFIX}${id}${dir}_type`).val())
    switch ($(`#${TRAVEL_PREFIX}${id}${dir}_type`).val()) {
        case 'PLANE':
            show_input_section(`${TRAVEL_PREFIX}${id}${dir}_otherSection`, false);
            show_input_section(`${TRAVEL_PREFIX}${id}${dir}_timeSection`, true);
            show_input_section(`${TRAVEL_PREFIX}${id}${dir}_planeSection`, true);
            break;
        case 'OTH':
            show_input_section(`${TRAVEL_PREFIX}${id}${dir}_otherSection`, true);
            show_input_section(`${TRAVEL_PREFIX}${id}${dir}_timeSection`, false);
            show_input_section(`${TRAVEL_PREFIX}${id}${dir}_planeSection`, false);
            break;
        default:
            show_input_section(`${TRAVEL_PREFIX}${id}${dir}_otherSection`, false);
            show_input_section(`${TRAVEL_PREFIX}${id}${dir}_timeSection`, false);
            show_input_section(`${TRAVEL_PREFIX}${id}${dir}_planeSection`, false);
    }        
}
// Show the number of guest entries depending on room type.
function set_room_guest_sections(id){
//    console.log(`Room type ${id} changed to ` + $(`#${ROOM_PREFIX}${id}type`).val())
    switch ($(`#${ROOM_PREFIX}${id}type`).val()) {
        case 'single':
            show_input_section(`${ROOM_PREFIX}${id}guest2Section`, false);
            show_input_section(`${ROOM_PREFIX}${id}guest3Section`, false);
            break;
        case 'double':
        case 'twin':
            show_input_section(`${ROOM_PREFIX}${id}guest2Section`, true);
            show_input_section(`${ROOM_PREFIX}${id}guest3Section`, false);
            break;
        case 'triple':
            show_input_section(`${ROOM_PREFIX}${id}guest2Section`, true);
            show_input_section(`${ROOM_PREFIX}${id}guest3Section`, true);
            break;
        default:    
            show_input_section(`${ROOM_PREFIX}${id}guest2Section`, false);
            show_input_section(`${ROOM_PREFIX}${id}guest3Section`, false);
            break;
    }
}
// Show or hide and enable or disable an input. Disable will skip validations, and form data sumbission.
function show_input_section(id, show) {
    if (show) {
        $(`#${id} *`).prop('disabled', false);
        $(`#${id}`).removeClass("invisible").addClass("visible").removeClass("collapse").addClass("collapse.show")
    } else {
        $(`#${id} *`).prop('disabled', true);
        $(`#${id}`).removeClass("visible").addClass("invisible").removeClass("collapse.show").addClass("collapse")
    }
}
// Show or hide a button.
function show_button_section(id, show) {
    if (show) {
        $(`#${id}`).removeClass("invisible").addClass("visible").removeClass("collapse").addClass("collapse.show")
    } else {
        $(`#${id}`).removeClass("visible").addClass("invisible").removeClass("collapse.show").addClass("collapse")
    }
}
// Show or hide a section of one for the detail types
function show_form_section(id, show) {
    if (show) {
        // Show section, set ID to zero and Enable all inputs.
        $(`#${id}`).removeClass("invisible").addClass("visible").removeClass("collapse").addClass("collapse.show");
        $(`#${id}id`).val('0').prop('disabled',false);
        $(`#${id} *`).prop('disabled',false);
    } else {
        // Hide section, set ID to -1 and Disable all inputs.
        $(`#${id}`).removeClass("visible").addClass("invisible").removeClass("collapse.show").addClass("collapse");
        $(`#${id}id`).val('-1').prop('disabled',true);
        $(`#${id} *`).prop('disabled',true);
    }
    $("#bookingform").data("changed",true);
}
// Check if element is visible
function is_visible(id) {
    return $(`#${id}`).hasClass("visible");
}

/* Button functions */
// Clear all fields for an object and set id to -1.
function clear_object_data(prefix, object, idx) {
    object.forEach(function(field){
        if (field == 'party') {
            // party is radio box with Y/N options. Not the cleanest of solutins, but works for now.
            $(`#${prefix}${idx}partyY`).prop("checked",false);
            $(`#${prefix}${idx}partyN`).prop("checked",false);
        } else {
            $(`#${prefix}${idx}${field}`).val('');
        }
    });
    $(`#${prefix}${idx}id`).val(-1);
}
// Copy all fields of an object, including id.
function copy_object_data(prefix, object, idx_from, idx_to) {
    if ($(`#${prefix}${idx_from}id`).val() != -1) {
        object.forEach(function(field){
            if (field == 'party') {
                // party is radio box with Y/N options. Not the cleanest of solutins, but works for now.
                $(`#${prefix}${idx_to}partyY`).prop("checked",$(`#${prefix}${idx_from}partyY`).prop("checked"));
                $(`#${prefix}${idx_to}partyN`).prop("checked",$(`#${prefix}${idx_from}partyN`).prop("checked"));
            } else {
                $(`#${prefix}${idx_to}${field}`).val($(`#${prefix}${idx_from}${field}`).val());
            }
        });
        $(`#${prefix}${idx_to}id`).val($(`#${prefix}${idx_from}id`).val());
    }
}

// Update the datalist for the room guest names.
function load_room_guest_names_list() {
    let lst = ""
    let names = []
    let name = ''
    for (id = 0; id < CONST_MAX_DELEGATES; id++) {
      if ($(`#${DELEGATE_PREFIX}${id}id`).val() != -1) {
        name = $(`#${DELEGATE_PREFIX}${id}first_name`).val() + ' ' + $(`#${DELEGATE_PREFIX}${id}last_name`).val()
        names.push(name)
        lst += '<option value="' + name + '"></option>'
      }
    }
    for (id = 0; id < CONST_MAX_GUESTS; id++) {
      if ($(`#${GUEST_PREFIX}${id}id`).val() != -1) {
        name = $(`#${GUEST_PREFIX}${id}first_name`).val() + ' ' + $(`#${GUEST_PREFIX}${id}last_name`).val()
        names.push(name)
        lst += '<option value="' + name + '"></option>'
      }
    }
//    console.log("Names list :"+lst)
    $("#listGuestNames").html(lst)
    
    // Update patterns for inputs
    let currentRoomsUsernames = names.join('|').replace(/[A-Z]/g, (char)=>{return '['+char+char.toLowerCase()+']'})
//    console.log(currentRoomsUsernames)
    let inputs = $('input[name^="room["][name*="][guest"]')
    inputs.each(function() {
      $(this).prop('pattern', currentRoomsUsernames);
    });

}

// Limit the dates for travels to the room arrival/departure dates selected.
function set_travel_dates(dir) {
    let dates = [];
    let date_list = {};
    let def_list;
    if (dir == 'arr') {
        def_list = OPTIONS_ARR_DATES;
    } else {
        def_list = OPTIONS_DEP_DATES;
    }
    for (id = 0; id < CONST_MAX_ROOMS; id++) {
        if ($(`#${ROOM_PREFIX}${id}id`).val() != -1) {
            date = $(`#${ROOM_PREFIX}${id}${dir}_date`).val();
            if (!(date in dates)) {
                dates.push(date);
            }
        }
    }
    if (dates.length > 0) { 
        dates.sort();
        dates.forEach((date) => date_list[date] = def_list[date])
    } else {
        date_list = def_list;
    }

    // For each travel date
    for (id = 0; id < CONST_MAX_TRAVELS; id++) {
        lst = '<option value="">Please select</option>';
        sel_date = $(`#${TRAVEL_PREFIX}${id}${dir}_date`).val();
        $.each(date_list, function(k,v) {
            // Keep currrently select value
            let sel = '';
            if (k == sel_date) {
                sel = ' selected';
            }
            lst += '<option value="' + k + `"${sel}>` + v + '</option>';
        });
        $(`#${TRAVEL_PREFIX}${id}${dir}_date`).html(lst);
    }
}
    

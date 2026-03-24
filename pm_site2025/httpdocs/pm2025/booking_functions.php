<?php
// Functions shared by the multiple booking_*.php files.
require_once 'config.php';

// Save get from option arrays.
function getSaveArray($arr,$key) {
  if ($arr != null) {
    if (isset($key) AND $key != '' AND array_key_exists($key, $arr)) { return $arr[$key]; }
  }
	return '';
}
// Format return response for any POST action.
function postReturn ($msg, $res = 'success', $logged_off = false) {
	$resp = array();
	$resp['result'] = $res;
	if (is_array($msg)) {
		foreach($msg as $key => $val) {
			$resp[$key] = $val;
		}
	} else {
		$resp['msg'] = $msg;
	}
	$resp['logged_off'] = $logged_off;
	echo json_encode($resp);
}

// Calculate the number of Hotel nights based on arrivale and departure dates.
function calculate_nights($arr_date, $dep_date) {
	$datetime1 = strtotime($arr_date);
	$datetime2 = strtotime($dep_date);
	if ($datetime1 > 0 and $datetime2 > 0) {
		$secs = $datetime2 - $datetime1;// == <seconds between the two times>
		$nights = $secs / 86400;
	} else {
		$nights = 0;
	}
	return $nights;
}
function format_charge($amount) {
	return PM_CURRENCY.number_format($amount, 2, ',', '.');
}
function format_date($date) {
	return date('d-M-Y', strtotime($date));
}
// Generate our default page header
function page_header($title) {
	$html  = '';
	$html .= '<div id="main-header" class="page_header">';
	$html .=  '<h1>'.SITE_TITLE_SHORT.' '.$title.'</h1>';
	$html .=  '<div class="row">';
	$html .=   '<div class="col-1 text-center"></div>';
	$html .=   '<div class="col-2 align-self-end text-center"><img alt="FHDCE logo" id="page_header_img3" class="fhdce-logo-large animate-width img-fluid" src="'.PM_FHDCE_LOGO.'"></div>';
	$html .=   '<div class="col-6 align-self-end text-center" id="page_header_img2_div"><img alt="PM logo" id="page_header_img2" class="pm-logo-large img-fluid" src="'.PM_LOGO.'"></div>';
	$html .=   '<div class="col-2 align-self-end text-center" id="page_header_img1_div"></div>';
	$html .=   '<div class="col-1 text-center"></div>';
	$html .=  '</div><br>';
	$html .= '</div>';
	return $html;
}

// Generate a form html input with label
function generate_form_row($label, $name, $width = 12, $place_holder = null, $type = "text", $min_len = null, $max_len = 250, $options = null, $pattern= null, $value = null, $id = null, $required=true, $disabled=false) {
	$html = '';
    if (!isset($id)) {
		$id = str_replace(array('[', ']'), "", $name);
	}
  $req = ($required) ? ' required':'';
	$dis = ($disabled) ? ' disabled':'';
	if ($type == 'checkbox' or $type == 'radio'){
      $html .= "<div class=\"col-auto\" id=\"$id" . "Section\">";
	    $html .= '<div class="pt-1 row">';
//	    $html .= '<div class="form-label col-auto"><label for="'.$id.'" class="form-check-label">'.$label.'</label></div>';
	    $html .= '<div class="form-label col-md-'.$width.'">'.$label.'</div>';
	    foreach ($options as $key => $option) {
		    $is_checked = ($key == $value) ? ' checked':''; 
		    $html .= '<div class="form-label col-auto">';
		    $html .= "<input type=\"$type\" class=\"form-check-input\" id=\"$id$key\" name=\"$name\" value=\"$key\"$is_checked$req$dis>";
		    $html .= "<label for=\"$id$key\" class=\"form-check-label\">&nbsp;$option</label>";
		    $html .= '</div>';
		    $req = '';
	    }
	    $html .= '<div class="invalid-feedback">You must make a choice.</div>';
	    $html .= '</div>';
	} else {
      $html .= "<div class=\"col-md-$width\" id=\"$id" . "Section\">";
	    $html .= "<label for=\"$id\" class=\"form-label\">$label</label>";
	    if ($type == 'select') {
    		$html .= "<select class=\"form-select\" id=\"$id\" name=\"$name\"$req$dis>";
	   		$selected = (isset($value)) ? $value : '--';
	    	$html .= '<option value="">Please select</option>';
		    foreach ($options as $key => $option) {
          $is_selected = ($key == $selected) ? ' selected':''; 
			    $html .= "<option$is_selected value=\"$key\">$option</option>"; 
    		};
	   		$html .= '</select>';
	    	$html .= '<div class="invalid-feedback">Make a selection!</div>';
    	} else {
	   		$html .= "<input type=\"$type\" class=\"form-control\" id=\"$id\" name=\"$name\"";
		    if ($type == 'number'){
	    		if (isset($min_len)) { $html .= " min=\"$min_len\"";}
			    if (isset($max_len)) { $html .= " max=\"$max_len\"";}
    			if (isset($options)) { 
	   				$html .= " step=\"$options\""; 
	    		} else {
		    		$html .= " step=\"1\""; 
			    }
    		} else {
	   			if (isset($min_len)) { $html .= " minlength=\"$min_len\"";}
	    		if (isset($max_len)) { $html .= " maxlength=\"$max_len\"";}
          if (isset($pattern)) { $html .= " pattern=\"$pattern\""; }
		    }
    		if (isset($options)) { $html .= " list=\"$options\""; }
	   		if (isset($place_holder)) { $html .= " placeholder=\"$place_holder\""; }
	    	if (isset($value)) { $html .= ' value="'.htmlspecialchars($value).'"'; }
		    $html .= "$req$dis />";
    		$html .= '<div class="invalid-feedback">Enter a value';
        if (isset($pattern) AND isset($options)) { $html .= ', or make sure the name matches a Delegate or Guest'; }
        $html .= '.</div>';
	   	}
	}
	$html .= '</div>';
	return $html;
}

// Styles and classes we use for Overview layouts on web and email.
function get_html_email_styles($email){
    $styles = array();
    if ($email) {
        $styles['h1'] = ' style="color:#FF6600;font-size:160%;"';
        $styles['h2'] = ' style="color:#FF6600;;font-size:140%;"';
        $styles['h3'] = ' style="color:#FF6600;font-size:120%;"';
        $styles['h4'] = ' style="color:#FF6600;"';
        $styles['tabst'] = ' style="margin-left:1rem;padding:0.25rem 0.25rem;width:90%;"';
        $styles['thdst'] = '';
        $styles['trstrst'] = ' style="background-color:rgb(222,226, 230);"';
        $styles['al_right'] = ' style="text-align:right;"';
        $styles['al_left'] = ' style="text-align:left;"';
        $styles['al_top'] = ' style="vertical-align:text-top;"';
        $styles['tab_al_top'] = ' style="margin-left:1rem;padding:0.25rem 0.25rem;width:90%;vertical-align:text-top;"';
        $styles['comment'] = ' style="width:100%;border-style: solid;border-color:rgb(222,226, 230);border-width: 1.0pt;"';
    } else {
        $styles['h1'] = '';
        $styles['h2'] = '';
        $styles['h3'] = '';
        $styles['h4'] = '';
        $styles['tabst'] = ' class="table table-striped table-sm"';
        $styles['thdst'] = ' class="thead-dark"';
        $styles['trstrst'] = '';
        $styles['al_right'] = ' class="charge"';
        $styles['al_left'] = '';
        $styles['al_top'] = '';
        $styles['tab_al_top'] = ' class="table table-striped table-sm"';
        $styles['comment'] = ' class="overview_comments"';
    }
    return $styles;
}

?>
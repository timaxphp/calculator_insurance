<?php

// include config file
require_once "config.php";

// request params
$act = isset($_GET['act']) ? trim($_GET['act']) : NULL;
$id = isset($_GET['id']) ? intval($_GET['id']) : NULL;

////////////////////////////////////////////////
// FUNCTIONS
////////////////////////////////////////////////

// send email message
function send_mime_mail(
	$name_from, // имя отправителя
	$email_from, // email отправителя
	$name_to, // имя получателя
	$email_to, // email получателя
	$data_charset, // кодировка переданных данных
	$send_charset, // кодировка письма
	$subject, // тема письма
	$body // текст письма
	) {
	
	$email_to = explode(',', trim(trim($email_to),','));

	foreach($email_to as $email)
	{
		$emails[] = mime_header_encode($name_to, $data_charset, $send_charset) . ' <' . trim($email) . '>';
	}
	$to = implode(',', $emails);
	
	$subject = mime_header_encode($subject, $data_charset, $send_charset);
	$from =  mime_header_encode($name_from, $data_charset, $send_charset) .' <' . $email_from . '>';
	
	if($data_charset != $send_charset) {
		$body = iconv($data_charset, $send_charset, $body);
	}
	
	$headers = "From: $from\r\n";
	$headers .= "Content-type: text/html; charset=$send_charset\r\n";
	$headers .= "Mime-Version: 1.0\r\n";
	
	return mail($to, $subject, $body, $headers);
}
// get mime header
function mime_header_encode($str, $data_charset, $send_charset) {
	if($data_charset != $send_charset) {
		$str = iconv($data_charset, $send_charset, $str);
	}
	return '=?' . $send_charset . '?B?' . base64_encode($str) . '?=';
}
// valid phone number
function is_phone($phone) {
	return preg_match('/^([0-9]{1,3}|1-[0-9]{1,3}) ([0-9]{1,10}) ([0-9]{2,3})-([0-9]{2,3})-([0-9]{2,3})$/is', $phone);
}
// valid email
function is_email($email) {
	return preg_match('/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', $email);
}

// ACT Controller
switch($act) {
case('send'):
	
	$fdata = $_GET['f'];
	$data = array();
	
	foreach($fdata as $key => $val)
	{
		switch($key) {
		case('drivers'):
			if(!empty($val))
			{
				$data[$key] = '';
				$drivers = array_chunk(explode(',', $val), 2);
				if(!empty($drivers))
				{
					foreach($drivers as $k => $driver)
					{
						$data[$key] .= "Возраст: {$driver[0]}, Стаж: {$driver[1]}; ";
					}
				}
			}
			break;
		case('mark'):
		case('model'):
			if($key == 'mark' && isset($mark[$val]))
			{
				$data['mark'] = $mark[$val]['name'];
				$data['model'] = isset($mark[$val]['models'][$fdata['model']]) ? $mark[$val]['models'][$fdata['model']] : NULL;
			}
			break;
		case('model'):
		default:
			$data[$key] = isset($step_data[$key][$val]) ? $step_data[$key][$val] : $val;
		}
	}
	
	// generate message
	$message = '
	<html>
	<head>
		<style>
			table#calc {}
			table#calc td {background:#f4f4f4;border:1px solid #ccc}
		</style>
	</head>';
	$message .= '
	<body>
	<table id="calc">';
	foreach($data as $key => $val)
	{
		switch($key) {
		case('stat_security'): $message .= '<tr><th colspan="2">Противоугонные системы</th></tr>'; break;
		case('drivers'): $message .= '<tr><th colspan="2">Водители</th></tr>'; break;
		case('garant'): $message .= '<tr><th colspan="2">Дополнительные параметры</th></tr>'; break;
		}
		
		if(in_array($key, array('stat_security','immobilaizer','technoblock','mech_security','sput_security')))
			$message .= '<tr><td colspan="2">'. $val .'</td></tr>';
		else
			if(isset($lang[$key])) $message .=  '<tr><td width="150"><b>'. $lang[$key] .'</b>:</td><td>'. $val .'</td></tr>';
	}
	$message .= '
	<tr><td width="150"><b>Дата</b>:</td><td>'. date('d.m.Y в H:i') .'</td></tr>
	<tr><td width="150"><b>IP</b>:</td><td>'. $_SERVER['REMOTE_ADDR'] .'</td></tr>
	</table>
	</body>
	</html>';
	
	// Send data
	$result = new stdClass;
	if(send_mime_mail($config['from'], $config['from_email'], $config['to'], $config['to_email'], 'utf-8', 'windows-1251', 'Новый заказ на расчет стоимости КАСКО', $message))
		$result->success = 'Сообщение успешно отправлено, с вами свяжутся в ближайшее время.';
	else
		$result->error = 'Возникла ошибка при отправке сообщения. Попробуйте еще раз.';

	die(json_encode($result));
	
	break;
case('get_models'):
	if($id && !empty($mark[$id]['models']))
	{
		// delete first value
		array_unshift($mark[$id]['models'], '');
		unset($mark[$id]['models'][0]);
		
		die(json_encode($mark[$id]['models']));
	}
	exit('false');
	break;
}

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
	<title>КАСКО Калькулятор</title>
	<meta http-equiv="content-type" content="text/html; charset=utf8" />
	
	<link href="style.css" rel="stylesheet" type="text/css" />
	<script src="js/jquery.js" type="text/javascript"></script>
	<script src="js/script.js" type="text/javascript"></script>
	<script src="js/calculator.js" type="text/javascript"></script>
</head>
<body>

<form name="form_data" id="form_data" method="post" enctype="multipart/form-data">
	<!-- step1 -->
	<input type="hidden" id="f_mark" name="f[mark]" value="" />
	<input type="hidden" id="f_model" name="f[model]" value="" />
	<input type="hidden" id="f_year" name="f[year]" value="" />
	<input type="hidden" id="f_probeg" name="f[probeg]" value="" />
	<input type="hidden" id="f_cost" name="f[cost]" value="" />
	<input type="hidden" id="f_valute" name="f[valute]" value="" />
	<input type="hidden" id="f_payed" name="f[payed]" value="" />
	<input type="hidden" id="f_owner" name="f[owner]" value="1">
	<!--<input type="hidden" id="f_bank" name="f[bank]" value="" />-->
	<!-- step2 -->
	<input type="hidden" id="f_stat_security" name="f[stat_security]" value="" />
	<input type="hidden" id="f_immobilaizer" name="f[immobilaizer]" value="" />
	<input type="hidden" id="f_technoblock" name="f[technoblock]" value="" />
	<input type="hidden" id="f_mech_security" name="f[mech_security]" value="" />
	<input type="hidden" id="f_sput_security" name="f[sput_security]" value="" />
	<!-- step 3 -->
    <input type="hidden" id="f_drivers" name="f[drivers]" value="" />
    <input type="hidden" id="f_multi_drive" name="f[multi_drive]" value="" />
	<!-- step 4 -->
    <input type="hidden" id="f_garant" name="f[garant]" value="" />
    <input type="hidden" id="f_strah_sum" name="f[strah_sum]" value="" />
    <input type="hidden" id="f_franchise" name="f[franchise]" value="" />
	<!-- step 5 -->
	<input type="hidden" id="f_region" name="f[region]" value="moscow">
	<input type="hidden" id="f_region_other" name="f[region_other]" value="" />
	<input type="hidden" id="f_phone" name="f[phone]" value="" />
	<input type="hidden" id="f_user_fio" name="f[user_fio]" value="" />
	<input type="hidden" id="f_user_email" name="f[user_email]" value="" />
   	<input type="hidden" id="f_osago" name="f[osago]" value="" />
	<!--<input type="hidden" id="f_pred_date" name="f[pred_date]" value="" />-->
</form>

<!-- Wrapper //-->
<div id="wrapper">
<div id="calc">
	
	<div id="calc_head">
		<div id="calc_step_title">Выберите параметры автомобиля:</div>
		<div id="calc_step_image">&nbsp;</div>
		<div id="calc_step_num">1</div>
	</div>
	
	<div id="calc_body">
	<table id="calc_body_table">
		<tbody>
		<tr>
			<td id="data_choose">
				<h3>Ваш выбор:</h3>
				<table class="choose">
                   <tbody>
                   		<tr id="s1">
                   			<td class="s1">1.</td>
                   			<td class="s2">
                   				<div><span id="f_mark_v"></span> <span id="f_model_v"></span></div>
                   				<div><span id="f_year_v"></span></div>
                   				<div><span id="f_probeg_v">с пробегом</span></div>
                   				<div><span id="f_cost_v"></span> <span id="f_valute_v"></span></div>
                   				<div><span id="f_payed_v"></span></div>
                   				<div><span id="f_owner_v">Физическое лицо</span> <span id="f_bank_v"></span></div>
                   			</td>
                   		</tr>
                   		<tr style="display: none" id="s2">
                   			<td class="s1">2.</td>
                   			<td class="s2">
								<div class="mb10">Противоугонные системы:</div>
                   				<div><span id="f_stat_security_v"></span></div>
                   				<div><span id="f_immobilaizer_v"></span></div>
                   				<div><span id="f_technoblock_v"></span></div>
                   				<div><span id="f_mech_security_v"></span></div>
                   				<div><span id="f_sput_security_v"></span></div>
                   			</td>
                   		</tr>
                   		<tr style="display: none" id="s3">
                   			<td class="s1">3.</td>
                   			<td class="s2">
								<div class="mb10">Водители:</div>
                   				<div><span id="f_drivers_v"></span></div>
                   			</td>
                   		</tr>
                   		<tr style="display: none" id="s4">
                   			<td class="s1">4.</td>
                   			<td class="s2">
                   				<div class="mb10">Дополнительные параметры:</div>
                   				<div><span id="f_garant_v"></span></div>
                   				<div><span id="f_strah_sum_v"></span></div>
                   				<div><span id="f_franchise_v"></span></div>
                   				</td>
                   		</tr>
                   		<tr style="display: none" id="s5">
                   			<td class="s1">5.</td>
                   			<td class="s2">
								<div class="mb10">Расчет стоимости КАСКО</div>
								<br/>
							</td>
                   		</tr>
                   </tbody>
				</table>
            </td>
			<td id="other_data">
				<div class="linkstable" id="calc_data">
					
					<!-- Main step container //-->
					<div class="main_step">

						<!-- Step 1 //-->
						<div id="step1">

							<div class="value">
								<select onchange="this.blur(); load_models(this,''); setValue('f_mark');" id="mark" class="sel">
									<option value="0">Марка автомобиля</option>
									<?php
										foreach($mark as $key => $value)
											echo '<option value="'. $key .'">'. $value['name'] .'</option>';
									?>
								</select>
							</div>

							<div class="value">
								<select onchange="this.blur(); setValue('f_model');" id="model" class="sel">
									<option value="0">Модель автомобиля</option>
								</select>
							</div>

							<div class="value">
								<select onchange="this.blur(); setValue('f_year');" id="year" class="sel">
								<option value="0">Год выпуска</option>
								<?php
									foreach($years as $key => $value)
										echo '<option value="'. $value .'">'. $value .'</option>';
								?>
								</select>
							</div>

							<div class="new_value">
								<img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('probeg','btn_check'); setValue('f_probeg');" style="cursor: pointer" id="probeg_img" class="image icheck">
								<label onclick="imgFormClick('probeg','btn_check'); setValue('f_probeg');">Автомобиль без автопробега</label>
								<input type="hidden" value="1" name="probeg" id="probeg">
							</div>

							<div class="new_value">
							<div class="clear"><span class="title">Стоимость автомобиля:</span></div>
							<div class="left">
								<input type="text" onkeyup="format_cost(this)" value="" onblur="change_class(this); setValue('f_cost');" onkeydown="if (event.keyCode == 13) setValue('f_cost');" style="text-align: center; width: 120px" class="inp_text" maxlength="20" id="cost">
							</div>
							<div class="sel2">
								<select onchange="this.blur(); setValue('f_valute');" id="valute" class="sel2">
									<?php
									foreach($currencies as $key => $value)
										echo '<option value="'. $key .'">'. $value .'</option>';
									?>
								</select>
							</div>
							<div class="clear"></div>
							</div>

							<div class="new_value">
							<span class="title">Автомобиль приобретен:</span>
							<div style="margin-top: 5px;">
								<img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('f_payed1','btn_check'); set_one('f_payed',1); setValue('f_payed');" style="cursor: pointer" id="f_payed1_img" class="image icheck">
								<label onclick="imgFormClick('f_payed1','btn_check'); set_one('f_payed',1); setValue('f_payed');">Наличные</label>
								<input type="hidden" value="1" name="f_payed1" id="f_payed1">
								<img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('f_payed2','btn_check'); set_one('f_payed',2); setValue('f_payed');" style="cursor: pointer; margin-left: 20px;" id="f_payed2_img" class="image icheck">
								<label onclick="imgFormClick('f_payed2','btn_check'); set_one('f_payed',2); setValue('f_payed');">В кредит</label>
								<input type="hidden" value="1" name="f_payed2" id="f_payed2" />
								<input type="hidden" value="0" name="payed" id="payed" />
							</div>
							</div>

							<div class="new_value">
							<span class="title">Собственником является:</span><br>
							<div style="margin-top: 5px;">
								<img src="img/forms/btn_check_2.png" alt="" onclick="imgFormClick('f_owner1','btn_check'); set_one('f_owner',1); setValue('f_owner');" style="cursor: pointer" id="f_owner1_img" class="image icheck">
								<label onclick="imgFormClick('f_owner1','btn_check'); set_one('f_owner',1); setValue('f_owner');">Физическое лицо</label>
								<input type="hidden" value="2" name="f_owner1" id="f_owner1">
								<img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('f_owner2','btn_check'); set_one('f_owner',2); setValue('f_owner');" style="cursor: pointer; margin-left: 20px;" id="f_owner2_img" class="image icheck">
								<label onclick="imgFormClick('f_owner2','btn_check'); set_one('f_owner',2); setValue('f_owner');">Юридическое лицо</label>
								<input type="hidden" value="1" name="f_owner2" id="f_owner2">
								<input type="hidden" value="1" name="owner" id="owner">
							</div>
							</div>

						</div>
						<!-- END Step 1 //-->

						<!-- Step 2 //-->
						<div style="display: none" id="step2">  <!-- step2 -->

							<div class="new_value">
								<img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('stat_security','btn_check'); setValue('f_stat_security');" style="cursor: pointer" id="stat_security_img" class="image icheck">
								<label onclick="imgFormClick('stat_security','btn_check'); setValue('f_stat_security');">Звуковая сигнализация</label>
								<input type="hidden" value="1" name="stat_security" id="stat_security">
								<a title="" onmouseover="showhint('Под звуковой сигнализацией понимается, что при нарушении покоя Вашего автомобиля будет автоматически включаться звуковая сирена.', this, event)" class="hintanchor" href="#"><img alt="" src="img/help.png" class="image"></a>
							</div>

							<div class="new_value">
								<img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('immobilaizer','btn_check'); setValue('f_immobilaizer');" style="cursor: pointer" id="immobilaizer_img" class="image icheck">
								<label onclick="imgFormClick('immobilaizer','btn_check'); setValue('f_immobilaizer');">Иммобилайзер</label>
								<input type="hidden" value="1" name="immobilaizer" id="immobilaizer">
								<a title="" onmouseover="showhint('Иммобилайзер это противоугонное электронное устройство, которое блокирует те или иные электронные узлы автомобиля при попытке угона. Как правило, штатно устанавливается на все современные автомобили.', this, event)" class="hintanchor" href="#"><img alt="" src="img/help.png" class="image"></a>
							</div>

							<div class="new_value">
								<img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('technoblock','btn_check'); setValue('f_technoblock');" style="cursor: pointer" id="technoblock_img" class="image icheck">
								<label onclick="imgFormClick('technoblock','btn_check'); setValue('f_technoblock');">Техноблок</label>
								<input type="hidden" value="1" name="technoblock" id="technoblock">
								<a title="" onmouseover="showhint('Противоугонная система, блокирующая гидравлические и пневматические системы тормозов и сцеплений автомобиля при попытке угона.', this, event)" class="hintanchor" href="#"><img alt="" src="img/help.png" class="image"></a>
							</div>

							<div class="new_value">
								<img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('mech_security','btn_check'); setValue('f_mech_security');" style="cursor: pointer" id="mech_security_img" class="image icheck">
								<label onclick="imgFormClick('mech_security','btn_check'); setValue('f_mech_security');">Механическая противоугонная система</label>
								<input type="hidden" value="1" name="mech_security" id="mech_security">
								<a title="" onmouseover="showhint('Подразумеваются блокирвки на капот, коробку передач, на руль и т.п.', this, event)" class="hintanchor" href="#"><img alt="" src="img/help.png" class="image"></a>
							</div>

							<div class="new_value">
								<img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('sput_security','btn_check'); setValue('f_sput_security');" style="cursor: pointer" id="sput_security_img" class="image icheck">
								<label onclick="imgFormClick('sput_security','btn_check'); setValue('f_sput_security');">Спутниковая противоугонная система</label>
								<input type="hidden" value="1" name="sput_security" id="sput_security">
								<a title="" onmouseover="showhint('Противоугонная система, имеющая связь со спутником? позволяющая удаленно определяють точные координаты нахождения Вашего автомобиля. При попытке угона система передает сигнал на спутник, после чего принмаются все возможные меры для предотвращения хищения автомобиля.', this, event)" class="hintanchor" href="#"><img alt="" src="img/help.png" class="image"></a>
							</div>

						</div>
						<!-- END Step 2 //-->
					
						<!-- Step 3 //-->
						<div style="display: none" id="step3">  <!-- step3 -->
							<div class="new_value">
							<div class="driv"><span class="title">Возраст</span><span style="margin-left: 73px;" class="title">Стаж</span></div>
								
								<input type="hidden" value="" id="drivers" name="drivers">
								<div class="left" id="all_drivers"></div>
								<div class="left"><a onclick="if (allow_act(this)) new_driver(); return false;" class="add_driver" href="#"><img src="img/add_2.png" style="margin-top: 0px;" id="add_driver" class="image"></a></div>
								<div class="clear"></div>
								<div>
										  <img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('multi_drive','btn_check'); activate_drivers(); setValue('f_multi_drive'); " style="cursor: pointer" id="multi_drive_img" class="image icheck">
										  <label onclick="imgFormClick('multi_drive','btn_check'); activate_drivers(); setValue('f_multi_drive'); ">Без ограничений (Multi Drive)</label> <a title="" onmouseover="showhint('К управлению автомобилем будут допущены любые водители без ограничений по стажу, возрасту, количеству.', this, event)" class="hintanchor" href="#"><img alt="" src="img/help.png" class="image"></a>
										  <input type="hidden" value="1" name="multi_drive" id="multi_drive">
								</div>

								
							</div>
						</div>
						<!-- END Step 3 //-->

						<!-- Step 4 //-->
						<div style="display: none" id="step4">
							<div class="new_value">
								<img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('garant','btn_check'); setValue('f_garant');" style="cursor: pointer" id="garant_img" class="image icheck">
								<label onclick="imgFormClick('garant','btn_check'); setValue('f_garant');">Автомобиль на гарантии дилера</label>
								<input type="hidden" value="1" name="garant" id="garant">
								<a title="" onmouseover="showhint('Отметье данный пункт, если Ваш автомобиль находится на заводской гарантии.', this, event)" class="hintanchor" href="#"><img alt="" src="img/help.png" class="image"></a>
							</div>

							<div class="new_value">
								<img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('strah_sum','btn_check'); setValue('f_strah_sum');" style="cursor: pointer" id="strah_sum_img" class="image icheck">
								<label onclick="imgFormClick('strah_sum','btn_check'); setValue('f_strah_sum');">Агрегатная страховая сумма</label>
								<input type="hidden" value="1" name="strah_sum" id="strah_sum">
								<a title="" onmouseover="showhint('Позволяет снизить стоимость КАСКО до 10%. В этом случае размер выплат, которые вы можете получить со страховой компании, при каждом страховом случае уменьшается. То есть, допустим, вам несколько раз машину отремонтировали, а потом у вас ее угнали &ndash; страховая компания выплатит Вам стоимость машины за вычетом стоимости всех ремонтов.', this, event)" class="hintanchor" href="#"><img alt="" src="img/help.png" class="image"></a>
							</div>

							<div class="new_value">
								<img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('franchise','btn_check'); setValue('f_franchise');" style="cursor: pointer" id="franchise_img" class="image icheck">
								<label onclick="imgFormClick('franchise','btn_check'); setValue('f_franchise');">Франшиза</label>
								<input type="hidden" value="1" name="franchise" id="franchise">
								<a title="" onmouseover="showhint('Позволяет снизить стоимость КАСКО до 40%. Франшиза это предусмотренное условиями страхования освобождение страховой компании от возмещения убытков Вам, не превышающих определенной суммы ущерба.', this, event)" class="hintanchor" href="#"><img alt="" src="img/help.png" class="image"></a>
							</div>

							<div style="margin-bottom: 5px;" class="newvalue">
								<span class="title">ФИО:</span><br>
								<input type="text" value="" onblur="change_class(this); setValue('f_user_fio');" style="width: 280px" class="inp_text" maxlength="128" id="user_fio">
							</div>

							<div class="new_value">
							<div class="clear"><span class="title">Выберите ваш регион:</span></div>

							<div style="margin-top: 5px;" class="value">
								<select onchange="this.blur(); other_region(this.value); setValue('f_region');" id="region" class="sel">
									<?php
									foreach($step_data['region'] as $key => $value)
										echo '<option value="'. $key .'">'. $value .'</option>';
									?>
								</select>
							</div>
							<div style="margin-top: 5px; width: 300px; display: none;" id="region_other2">
									<input type="text" value="введите ваш город" style="text-align: center; width: 280px;" class="inp_text" maxlength="64" onblur="setValue('f_region_other'); change_class(this); if (this.value == '') this.value = 'введите ваш город';" onfocus="if (this.value == 'введите ваш город') this.value = '';" onclick="if (this.value == 'введите ваш город') this.value = '';" onkeydown="change_class(this); if (event.keyCode == 13) { setValue('f_region_other'); show_generator();}" id="region_other">
							</div>
							</div>
							<div style="margin: 10px 0 10px 0;" class="newvalue">
								<img src="img/forms/btn_check_1.png" alt="" onclick="imgFormClick('osago','btn_check'); setValue('f_osago');" style="cursor: pointer" id="osago_img" class="image icheck">
								<label onclick="imgFormClick('osago','btn_check'); setValue('f_osago');">Мне еще нужен полис ОСАГО</label>
								<input type="hidden" value="1" name="osago" id="osago">
							</div>
						</div>
						<!-- END Step 4 //-->

						<!-- Step 5 //-->
						<div style="display:none;" id="step5">
							
							<div id="contacts" class="newvalue">
								Укажите Ваш мобильный номер телефона, на который будет выслан результат расчета стоимости полиса КАСКО в 25 страховых компаниях.
							<div class="newvalue" style="margin: 10px 0;">
							<span class="title">+7</span> <input id="phone" class="inp_text" type="text" maxlength="13" onkeyup="setPhoneFormat(this,event); setValue('f_phone');" onchange="setValue('f_phone');" onkeydown="change_class(this); setValue('f_phone'); if (event.keyCode == 13) send_cost();" style="width: 130px;" value="">
								<a href="#" onclick="send_cost(); return false;" title="" class="btn">Отправить</a>
							</div>
							<div class="newvalue" style="margin-bottom: 5px;">
								Удобство заключается в доступности расчетов в любое время, стоит просто открыть SMS! Данный сервис абсолютно <font style="font-weight: bold; color: red;">бесплатен</font> для Вас.
							</div>
							
						</div>
						<!-- END Step 5 //-->

					</div>
					<!-- END Main step container //-->
					
					<div class="buts">
						<div style="display:none;" class="left button" id="left_but">
							<a title="Назад" onclick="prev_step(); return false;" href="#" class="btn">
								<span>Назад</span>
							</a>
						</div>
						<div class="right button" id="right_but">
							<a title="Далее" onclick="next_step(); return false;" href="#" class="btn">
								<span>Далее</span>
							</a>
						</div>
					</div>

				</div>
			</td>
		</tr>
		</tbody>
	</table>
</div>
	</div>
	
</div>
<!-- END Wrapper //-->

</body>
</html>
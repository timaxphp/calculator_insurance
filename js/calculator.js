////////////////////////////////
// КАЛЬКУЛЯТОР
////////////////////////////////

deleteCookie('stop', "/");
addCookie('step', 1, 1, "/");

var step_data = {
	text: {
		1: 'Выберите параметры автомобиля:',
		2: 'Противоугонные системы авто:',
		3: 'Лица допущенные к управлению:',
		4: 'Дополнительные параметры:',
		5: 'Расчет стоимости КАСКО:'
    },
    img: { 1: 1, 2: 2, 3: 3, 4: 3, 5: 3 },
    values: { 1: 'f_mark', 2: 'f_model', 3: 'f_year', 4: 'f_cost', 5: 'f_security', 6: 'f_payed', '6_1': 'f_bank', 7: 'f_owner', 8: 'f_drivers', 9: 'f_phone', 10: 'f_discount' },
	probeg: {
		1: 'с пробегом',
		2: 'без пробега'
    },
	valute: {
		'rur': 'рублей',
		'usd': 'долларов США',
		'eur': 'ЕВРО'
    },
	stat_security: {
		1: '',
		2: 'Звуковая сигнализация'
    },
	immobilaizer: {
		1: '',
		2: 'Иммобилайзер'
    },
	technoblock: {
		1: '',
		2: 'Техноблок'
    },
	mech_security: {
		1: '',
		2: 'Механическая ПС'
    },
	sput_security: {
		1: '',
		2: 'Спутниковая ПС'
    },
	payed: {
		1: 'Наличные',
		2: 'В кредит'
    },
	garant: {
		1: '',
		2: 'на гарантии'
    },
	strah_sum: {
		1: '',
		2: 'агрегатная страховая сумма'
    },
	franchise: {
		1: '',
		2: 'франшиза'
    },
	owner: {
		1: 'Физическое лицо',
		2: 'Юридическое лицо'
    }
}

// show tooltip
function showhint(msg, el, e)
{
	var self = $(el), offset = self.offset();
	var popup = $('<div>'+ msg +'</div>')
		.addClass('hint')
		.css({position: 'absolute'})
		.css({top: offset.top})
		.css({left: offset.left + self.outerWidth() + 15});
	
	$('body').append(popup);
	self.mouseout(function(){ popup.remove(); });
}

// end step message
function close_message(status, text, focus, action)
{
	alert(text);
}

//
function format_cost(e){
   var val = e.value;
   if (val.length>0) {
    val = val.replace(/[^0-9]/g, "");
   	var j = 0;  var new_value = '';
   	if (val.length>=3){
   		for (var i=val.length-1; i>=0; i--) {
   		j++;
   		new_value = val.charAt(i)+new_value;
   		if (j%3==0) new_value = ' '+new_value;
   		}
   	} else new_value = val;
    e.value = trim(new_value);
   }
}

// --------------------
// ВОДИЛЫ
// --------------------

// allow_act
function activate_drivers()
{
	var value = doc('multi_drive').value;
	if (value != 2) value = 1;

	var selects = doc('all_drivers').getElementsByTagName('select');
	if (selects.length > 0) for (var i=0; i<selects.length; i++) {
		selects[i].disabled = (value == 2) ? true : false;
	}

	var imgs = doc('all_drivers').getElementsByTagName('img');
	var new_value = (value == 2) ? 1 : 2;
	var repl = new RegExp(value,"i");
	if (imgs.length > 0) for (var i=0; i<imgs.length; i++) imgs[i].src = imgs[i].src.replace(repl,new_value);
	doc('add_driver').src = doc('add_driver').src.replace(repl,new_value);
	//if (value == 2) doc('f_drivers').value = 'multi';
	setValue('f_drivers');
}

// allow_act
function allow_act(e)
{
	e.blur();
	if (/2/i.test(e.getElementsByTagName('img')[0].src)) return true;
	else return false;
}

// del_driver
function del_driver(id)
{
	var len = get_drivers_length();
	if (len == 1) close_message('info','Удалить всех лиц, допущенных к управлению нельзя!');
	else if (doc('driver_'+id) && doc('all_drivers')) {
		doc('all_drivers').removeChild(doc('driver_'+id));
		set_height();
	}
}

// get_drivers_length
function get_drivers_length()
{
	return doc('all_drivers').getElementsByTagName('div').length;
}

// set_drivers_value
function set_drivers_value()
{
	var value = '';
	var selects = doc('all_drivers').getElementsByTagName('select');
	if (selects.length > 0) for (var i=0; i<selects.length; i++) {
	 value += selects[i].value+',';
	 //doc('f_drivers_v').innerHTML = 'asdasdad'; //+= selects[i].value+'<br>';
	}
	if (value.length>0) value = value.substr(0,value.length-1);
	doc('drivers').value = value;
}

// set_height
function set_height()
{
	// var len = get_drivers_length();
	set_drivers_value();
	activate_drivers();
	reloadPage();
}

// new_driver
function new_driver(age, stage)
{
	if (age == undefined) age = -1;
	if (stage == undefined) stage = -1;
    var id = get_drivers_length();
	var div = document.createElement('div');
	div.id = 'driver_'+id;
	div.className = 'driver';
	var select_age = document.createElement('select');
	select_age.id = 'age_'+id;
	select_age.name = 'age_'+id;
	select_age.className = 'age';
	select_age.onchange = new Function("set_stage(this,"+id+"); setValue('f_drivers');");

	var option = document.createElement('option');
    option.value = 'none';
    option.innerHTML = 'Возраст';
	select_age.appendChild(option);
	for (var i=18; i<=61; i++) {
	var option = document.createElement('option');
   	 	option.value = i;
    	option.innerHTML = (i!=61) ? i : '61 и более';
    	option.selected = (i==age || (i==61 && age>=61)) ? true : false;
		select_age.appendChild(option);
	}

	var select_stage = document.createElement('select');
	select_stage.id = 'stage_'+id;
	select_stage.name = 'stage_'+id;
	select_stage.className = 'stage';
	select_stage.onchange = new Function("set_drivers_value(); setValue('f_drivers');");

	var option = document.createElement('option');
    option.value = 'none';
    option.innerHTML = 'Стаж';
	select_stage.appendChild(option);
	var option = document.createElement('option');
    option.value = '0';
    option.selected = (stage==0 || age == 18) ? true : false;
    option.innerHTML = 'Нет';
	select_stage.appendChild(option);
	var from = (age == 18) ? 0 : 1;
	var end = ( ((age-18) < 10 && (age-18) > 0) || age == 18) ? age-18 : 10;
	for (var i=from; i<=end; i++) if (i>0) {
	var option = document.createElement('option');
   	 	option.value = i;
    	option.innerHTML = (i!=10) ? i : '10 и более';
    	option.selected = (i==stage || (i==end && stage>=end)) ? true : false;
		select_stage.appendChild(option);
	}
	
	var del_image = document.createElement('img');
	del_image.className = 'image';
	del_image.src = 'img/del_2.png';
	del_image.alt = '';
	
	var del_href = document.createElement('a');
	del_href.className = 'del_driver';
	del_href.href = '#';
	del_href.onclick = new Function("if (allow_act(this)) del_driver("+id+"); return false;");
	del_href.appendChild(del_image);
	
	div.appendChild(select_age);
	div.appendChild(document.createTextNode(' '));
	div.appendChild(select_stage);
	div.appendChild(document.createTextNode(' '));
	div.appendChild(del_href);
	doc('all_drivers').appendChild(div);

    set_height();
}

// set_stage
function set_stage(e,id)
{
	var select_stage = doc('stage_'+id);
    var stage = select_stage.value;
	select_stage.length = 2;
	var age = (parseInt(e.value)>0) ? parseInt(e.value) : -1;

	var from = (age == 18) ? 0 : 1;
	var end = ( ((age-18) < 10 && (age-18) > 0) || age == 18) ? age-18 : 10;
	for (var i=from; i<=end; i++) if (i>0) {
	var option = document.createElement('option');
   	 	option.value = i;
    	option.innerHTML = (i!=10) ? i : '10 и более';
    	option.selected = (i==stage || (i==end && stage>=end)) ? true : false;
		select_stage.appendChild(option);
	}
	//if (age == 18) select_stage.value = '';
	set_drivers_value();
}

// hide_drivers
function hide_drivers()
{
	var selects = doc('all_drivers').getElementsByTagName('select');
	if (selects.length > 0) for (var i=0; i<selects.length; i++) selects[i].disabled = true;

	var imgs = doc('all_drivers').getElementsByTagName('img');
	var new_value = 1;
	var repl = new RegExp(2,"i");
	if (imgs.length > 0) for (var i=0; i<imgs.length; i++) imgs[i].src = imgs[i].src.replace(repl,new_value);
	doc('add_driver').src = doc('add_driver').src.replace(repl,new_value);
}

///// >>>>> ВОДИТЕЛИ

// check_step
function check_step()
{
	var current_step = getCookie('step');
	var stop = getCookie('stop');
	if (stop=='yes' && current_step<=11) { close_message('info','Вернуться к предыдущим пунктам невозможно!<br>Возможно только <a href="#" onclick="begin(); return false;">начать сначала</a>'); return false; }
	return true;
}

// begin
function begin()
{
	//if ($('acts')) $('acts').style.display = 'none';
	close_message();
	clear_steps();
	step(1);
}

// load models
var num_bg;
$(function(){
	num_bg = $('#calc_step_image').css('backgroundImage');
});
	
function load_models(e)
{
	var id = $(e).val(),
		model = $('#model').html('');
	
	if(num_bg != undefined) $('#calc_step_image').css({backgroundImage: num_bg});
	
	// empty mark
	if(id == 0)
	{
		model.html('<option value="0">Модель автомобиля</option>');
		return;
	}
	
	var img_src = 'img/auto/'+ id +'.png';
	var img = $('<img src="'+ img_src +'" alt=""/>').hide();
	$('body').append(img);
	
	img.load(function() {
		$('#calc_step_image').css({backgroundImage: 'url('+ img_src +')'});
	});
	
	$.ajax({
		url: 'index.php?act=get_models&id=' + id,
		dataType : "json",
		success: function (data, textStatus) {
			if(data)
			{
				model.append('<option value="0">Выберите модель</option>');
				$.each(data, function(i, val) {
					model.append('<option value="'+ i +'">'+ val +'</option>');
				});
			}
			else
			{
				model.html('<option value="0" selected="selected" disabled="disabled">Моделей не найдено</option>');
			}
		},
	});
}

// setValue
function setValue(name,value)
{
	var field_name = name.replace(/^f_/i,"");
	value = (doc(field_name)) ? doc(field_name).value : '';
    doc(name).value = value;
	
	if (name.length>0 && name!=undefined)
	{
		addCookie(name, value, 1, "/");
		var new_value = '';

 		switch(doc(field_name).type){
			case 'hidden': new_value = (step_data[field_name] && step_data[field_name][value]!=undefined) ? step_data[field_name][value] : ''; break;
 			case 'select-one': if (field_name == 'valute') new_value = doc(field_name).options[doc(field_name).selectedIndex].innerHTML;
 							   else if (doc(field_name).selectedIndex!=0) new_value = doc(field_name).options[doc(field_name).selectedIndex].innerHTML; break;
        	default: new_value = value;
        }

		if (field_name == 'region' || field_name == 'region_other') {
			//alert(value + ' = ' +new_value);
			return false;
		}

 		switch(field_name)
		{
 			case 'mark': if (doc('model').selectedIndex==0){
 						 doc('f_model_v').innerHTML = '';
 						 doc('f_model').value = '';
 						 }
 						 break;
 			case 'model': if (new_value) new_value = '('+new_value+')'; break;
 			case 'year': if (doc('year').selectedIndex!=0) new_value = 'год выпуска '+new_value; break;
 			case 'cost': if (new_value) {
 						 	doc('f_valute_v').innerHTML = doc('valute').options[doc('valute').selectedIndex].innerHTML;
 						 	doc('f_valute').value = doc('valute').value;
 						 } else {
 						 	doc('f_valute_v').innerHTML = '';
 						 	doc('f_valute').value = '';
 						 }
 						 break;
 			case 'valute': if (!doc('f_cost').value) new_value = ''; break;
 			case 'drivers': var selects = doc('all_drivers').getElementsByTagName('select');
							if (selects.length > 0) for (var i=0; i<selects.length; i++) {
							  	 new_value += (i%2==0?'Возраст: ':'Стаж: ')+(selects[i].value=='none'?'':selects[i].value)+(i%2==0?', ':'<br>');
							  }
							if (doc('multi_drive').value==2) new_value = 'Без ограничений';
							  break;
			case 'multi_drive': if (value==2) doc('f_drivers_v').innerHTML = 'Без ограничений'; break;
 		}
 		if (doc(name+'_v')) doc(name+'_v').innerHTML = new_value;
	}
}

// set one
function set_one(name,id)
{
	var other = (id == 2) ? 1 : 2;
	var setname = name.substring(2,name.length);
	var value = doc(name+id).value;
	if (value == 2) {
		doc(setname).value = id;
		if (doc(name+other).value == 2) imgFormClick(name+other,'btn_check');
	} else if (doc(name+other).value == 1) doc(setname).value = 0;
}

// show step
function show_step(cur_step,step)
{
	if (step==undefined) step = getCookie('step');
	if (step == '' || step == undefined || step == null || step > 5) step = 1;
	if (step == 3 && doc('all_drivers').innerHTML=='') new_driver();
	
	if (checkErrors(cur_step) == false)
	{
		step_data['img'][step] = step_data['img'][step] || 1;
		
		var n = parseInt(step)-1;
		// Верхняя полоса Номер, Фон, Текст
		if (step>=6) addCookie('stop', 'yes', 1, "/");
		doc('calc_step_num').innerHTML = step;
		doc('calc_step_title').innerHTML = (step_data['text'][step]!=undefined) ? step_data['text'][step] : '';
		// doc('calc_step_image').style.backgroundImage = "url('img/step"+ step_data['img'][step] +".png')";
		
		// toggle buttons
		if (step<=1) {
			$('#right_but').show();
			$('#left_but').hide();
		} else if (step>=5) {
			$('#right_but, #left_but').hide();
		} else {
			$('#right_but, #left_but').show();
		}
	   
	   // toggle steps
		for(var i=1; i<=5; i++) $('#step'+i).hide();
		$('#step'+step).fadeIn('fast');
		
		if ($('#s'+step).length) $('#s'+step).show();
		
		addCookie('step', step, 1, "/");
		reloadPage();
	}
}

// checkErrors
function checkErrors(step)
{
	var result = true;
	step = parseInt(step);
	
	switch (step) {
	case 1: if (doc('mark').value == 0) { close_message('error','Необходимо указать "Марку автомобиля"', 'mark'); return true; }
			//if (doc('model').value == 0) { close_message('error','Необходимо указать "Модель автомобиля"', 'model'); return true; }
			if (doc('year').value == 0) { close_message('error','Необходимо указать "Год выпуска"', 'year'); return true; }
			if (doc('cost').value == 0) { close_message('error','Необходимо указать "Стоимость автомобиля"', 'cost'); return true; }
			if (doc('payed').value == 0) { close_message('error','Необходимо указать значение "Автомобиль приобретен"'); return true; }
			if (doc('owner').value == 0) { close_message('error','Укажите кто является собственником автомобиля'); return true; }
			result = false;
			break;
	case 2:
			result = false;
			break;
	case 3: if (doc('multi_drive').value==1){
				var selects = doc('all_drivers').getElementsByTagName('select');
				if (selects.length > 0) for (var i=0; i<selects.length; i++)
						if (selects[i].value == 'none') { close_message('error','Укажите '+(i%2==0?'возраст':'стаж')+' водителя',selects[i].id); return true; }
			}
			result = false;
			break;
	case 4:
			result = false;
			break;
	case 5:
			result = false;
			break;
	}
	
	return result;
}

// next_step
function next_step()
{
	var step = getCookie('step');
	if (step == '' || step == undefined || step == null || step > 5) step = 1;
	var cur_step = step;
	step++;
	show_step(cur_step,step);
	if (step==5 && $('phone')) $('phone').focus();
}

// prev_step
function prev_step()
{
	var step = getCookie('step');
	if (step == '' || step == undefined || step == null || step > 5) step = 1;
	var cur_step = step;
	step--;
	show_step(cur_step,step);
}

// clear_step
function clear_step(num)
{
   if (step_data['values'][num]==undefined) return false;
   doc(step_data['values'][num]).value = '';
}

// clear_steps
function clear_steps(id)
{
	var inputs = doc('form_data').getElementsByTagName('input');
	if (inputs.length) for (var i=0; i<inputs.length; i++) if (id == undefined || (id != undefined && inputs[i].id != id)) {
	  inputs[i].value = '';
	  deleteCookie(inputs[i].id, "/");
	  if (doc(inputs[i].id+'_v')) doc(inputs[i].id+'_v').innerHTML = '';
	}
	if (id == undefined) {
		deleteCookie('step', "/");
		deleteCookie('stop', "/");
		if (doc('acts')) doc('acts').style.display = 'none';
	}
	
	$('.choose').hide();
}

// clear_all
function clear_all()
{
	clear_steps();
	prev_step();prev_step();prev_step();prev_step();
	doc('s2').style.display = 'none';
	doc('s3').style.display = 'none';
	doc('s4').style.display = 'none';
	doc('s5').style.display = 'none';
}

function setPhoneFormat(e,event){
   var k = event.keyCode;
	   if (k != 8 && k != 46){
	   var val = e.value;
	   val = val.replace(/[^0-9]/g, "");
	   var pattern = /\(?([0-9]{3})\)?\ ?([0-9]{0,7})?/;
	   if (pattern.test(val)) val = '('+RegExp.$1+') '+RegExp.$2;
	   e.value = val;
   }
}

function other_region(value){
   if (value == 'other') {
      doc('region_other2').style.display = '';
   } else {
   	  doc('region_other2').style.display = 'none';
   }
}

function send_cost()
{
    if (doc('phone').value.length==13)
	{
		var phone = '+7'+doc('phone').value.replace(/[^0-9]/ig,"");
		
		$('#calc_body').html('Идет отправка...');
		
		$.ajax({
			url: 'index.php?act=send',
			data: $('#form_data').serialize() + '&phone=' + phone,
			dataType: "json",
			success: function (data) {
					
				if(data.success != undefined)
					$('#calc_body').html('<div class="success">'+ data.success +'</div>');
				else if(data.error != undefined)
					$('#calc_body').html('<div class="error">'+ data.error +'</div>');
				else
					$('#calc_body').html('<div class="error">По техническим причинам сервис не работает.</div>');
				
				console.log(data);
				clear_steps();
			},
			error: function() {
				$('#calc_body').html('<div class="error">По техническим причинам сервис не работает.</div>');	
			}
		});
	}
	else if (doc('phone').value=='') close_message('error','Необходимо указать телефон','phone');
	else close_message('error','Телефон указан некорректно','phone');
}
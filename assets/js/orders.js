// start jquery
$(document).ready(function() {

	// btn_return_sn
	$('.btn_return_sn').on('click', function () {
		btn = $(this)
      $.ajax({
         url: "/orders/get.php?return_sn",
         type: "POST",
         dataType: "html",
         data: ({ id: btn.data('id'), }),
         success: function(data){ 
            if (data == 'yes') location.reload();
            console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})



	// 
	$('.on_delete').on('click', function () {
		btn = $(this)
      $.ajax({
         url: "/orders/get.php?delete",
         type: "POST",
         dataType: "html",
         data: ({ id: btn.data('id'), }),
         success: function(data){ 
            if (data == 'yes') location.reload();
            console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})


   // 
   $('html').on('change', '.on_staff', function () {
      // id = $(this).children('option:selected').attr('data-id')
		btn = $(this)
      $.ajax({
         url: "/orders/get.php?change_staff",
         type: "POST",
         dataType: "html",
         data: ({ 
            id: btn.children('option:selected').attr('data-id'),
            order_id: btn.attr('data-order-id'),
         }),
         success: function(data){ 
            // if (data == 'yes') location.reload();
            console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})
   
   $('html').on('change', '.on_status', function () {
      // id = $(this).children('option:selected').attr('data-id')
		btn = $(this)
      $.ajax({
         url: "/orders/get.php?change_status",
         type: "POST",
         dataType: "html",
         data: ({ 
            id: btn.children('option:selected').attr('data-id'),
            order_id: btn.attr('data-order-id'),
         }),
         success: function(data){ 
            if (data == 'yes') location.reload();
            console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})
   $('html').on('change', '.on_type', function () {
      // id = $(this).children('option:selected').attr('data-id')
		btn = $(this)
      $.ajax({
         url: "/orders/get.php?change_type",
         type: "POST",
         dataType: "html",
         data: ({ 
            id: btn.children('option:selected').attr('data-id'),
            order_id: btn.attr('data-order-id'),
         }),
         success: function(data){ 
            if (data == 'yes') location.reload();
            console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})


   $('html').on('change', '.on_staff', function () {
      btn = $(this)
      $.ajax({
         url: "/orders/get.php?change_staff",
         type: "POST",
         dataType: "html",
         data: ({ 
            id: btn.children('option:selected').attr('data-id'),
            order_id: btn.attr('data-order-id'),
         }),
         success: function(data){ 
            if (data == 'yes') location.reload();
            console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})
   
   
   
   // 
	$('.on_sort_status').on('change', function () {
      var val = $(this).children('option:selected').attr('data-id');
      const url = new URL(window.location);
      url.searchParams.set('status', val); 
      history.pushState(null, null, url);
      location.reload();
	})
   // 
	// $('.on_sort_time').on('change', function () {
   //    var val = $(this).children('option:selected').attr('data-val');
   //    const url = new URL(window.location);
   //    url.searchParams.set('time', val);
   //    history.pushState(null, null, url);
   //    location.reload();
	// })

   $('.on_sort_time').on('change', function () {
      var val = $(this).val();
      const url = new URL(window.location);
      url.searchParams.set('time', val);
      history.pushState(null, null, url);
      location.reload();
   })
   
   
   // 
	$('.on_sort_type').on('change', function () {
      var val = $(this).children('option:selected').attr('data-id');
      const url = new URL(window.location);
      url.searchParams.set('type', val); 
      history.pushState(null, null, url);
      location.reload();
	})

   // 
	$('.on_sort_staff').on('change', function () {
      var val = $(this).children('option:selected').attr('data-id');
      const url = new URL(window.location);
      url.searchParams.set('staff', val); 
      history.pushState(null, null, url);
      location.reload();
	})


   // 
	$('.on_sort_branch').on('change', function () {
      var val = $(this).children('option:selected').attr('data-id');
      const url = new URL(window.location);
      url.searchParams.set('branch', val); 
      history.pushState(null, null, url);
      location.reload();
	})


















   $('html').on('click', '.on_print', function () {
      window.open("/orders/" + "order_print.php?" + "&orderID=" + $(this).attr('data-id'), "mywin","width=570,height=570,left=250,top=50");
	})







   // cashbox_pay
	$('.on_info').click(function(){
		$('.on_info_block').addClass('pop_bl_act');
		$('#html').addClass('ovr_h');

      $.ajax({
         url: "/orders/info.php",
         type: "POST",
         dataType: "html",
         data: ({ 
            id: $(this).attr('data-id'),
         }),
         success: function(data){
            $('.osigoi').html(data)
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})
	$('.on_info_back').click(function(){
		$('.on_info_block').removeClass('pop_bl_act');
		$('#html').removeClass('ovr_h');
	})














	// cashbox_pay
	$('.cashbox_pay').click(function(){
		$('.cashbox_pay_block').addClass('pop_bl_act');
		$('#html').addClass('ovr_h');
	})
	$('.cashbox_pay_back').click(function(){
		$('.cashbox_pay_block').removeClass('pop_bl_act');
		$('#html').removeClass('ovr_h');
	})

   // cashbox_pay
	$('.cashbox_pay2').on('click', function () {
		btn = $(this)
      $.ajax({
         url: "/orders/get.php?cashbox_pay",
         type: "POST",
         dataType: "html",
         data: ({ 
            number: $('.order_number_sel').attr('data-val'),
            total: $('.btype_totol').attr('data-val'),
            delivery: $('.btype_delivery').attr('data-val'),
            qr: $('.btype_qr').attr('data-val'),
         }),
         success: function(data){
            if (data == 'yes') location.reload();
            else if (data == 0) mess(w('Fill all fields', 'Барлық өрісті толтырыңыз'))
            console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})








   // cashbox_search
   $('.order_search_in').on('input', function() {
      if ($('.order_search_in').val() == '') {
         $('.bl_ors').addClass('dsp_n')
         $('.bl_orm').removeClass('dsp_n')
      } else {

         $.ajax({
            url: "/orders/search.php?order_search",
            type: "POST",
            dataType: "html",
            data: ({ 
               result: $('.order_search_in').val(),
            }),
            success: function(data){
               
               $('.bl_ors').removeClass('dsp_n')
               $('.bl_orm').addClass('dsp_n')

               $('.bl_ors .uc_u').html(data)
               $('.lazy_img').lazy({effect:"fadeIn", effectTime:300, threshold:0})
               console.log(data)
            },
            beforeSend: function(){ },
            error: function(data){ }
         })
      }
   })














}) // end jquery
<script type="text/javascript"> 
var data_tbl = '';
    function validateEmail($email) {
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        return emailReg.test($email);
    }


    function getSmsTabIndex() {
        if ($(".sms_tab li").hasClass('active')) {
            sms_tab_id = $(".sms_tab li.active").attr('id');
            sms_tab_id = sms_tab_id.replace('-1', '');
        }
        return sms_tab_id;
    }

    function getEmailTabIndex() {
        if ($(".email_tab li").hasClass('active')) {
            email_tab_id = $(".email_tab li.active").attr('id');
            email_tab_id = email_tab_id.replace('-2', '');
        }
        return email_tab_id;
    }

    function getAppmsgTabIndex() {
        if ($(".appmsg_tab li").hasClass('active')) {
            appmsg_tab_id = $(".appmsg_tab li.active").attr('id');
            appmsg_tab_id = appmsg_tab_id.replace('-3', '');
        }
        return appmsg_tab_id;
    }

    function getWhatsappTabIndex() {
        if ($(".whatsapp_tab li").hasClass('active')) {
            whatsapp_tab_id = $(".whatsapp_tab li.active").attr('id');
            whatsapp_tab_id = whatsapp_tab_id.replace('-4', '');
        }
        return whatsapp_tab_id;
    }

    function setTemplateValue(TID, obj,obj1,obj2,obj3) {
        $.ajax({
            type: "get",
            async: false,
            url: "<?= site_url('smsdashboard/template_details') ?>",
            data: {template_id: TID},
            dataType: "json",
            success: function (data) { 
                if (data['res_type'] == 1 || data['res_type'] == 3) {
                   
                    if (data['res_type'] == 1) {
                        var length = data['res_content_length']+34;
                        var smsId = getSmsTabIndex();
                       
                        if (length > 250) {
                            alert("Exceed the sms lenght");
                             obj.html('');
                        } else {
                        $('#'+smsId+' .max_sms_chars').text('SMS count : '+Math.ceil(length/smsLength )+' ');
                        $('.sms_length').val(Math.ceil(length/smsLength ));
                            obj.html(data['res_content']);
                         $('#'+smsId+' .sms_preview').css('display','block');
              		 $('#'+smsId+' .sms_preview_d').text(data['res_content']);
                        }
                    } else {
                      obj.html(data['res_content']);
                      obj1.val(data['res_subject']);
                      obj2.val(data['attachment']);
                      obj3.html(data['attachment_op']);
                    }
                } else {
                
                    obj.redactor('set', data['res_content']);
                    obj1.val(data['res_subject']); 
                    obj2.val(data['attachment']);
                    obj3.html(data['attachment_op']);
                }
            },
            error: function () {
            }
        });
    }
        
    $(document).ready(function () {
       
        /*---------------- Customer Autopopulate for SMS--------------------*/
        $.ajax({
            type: "get",
            async: true,
            url: "<?= site_url('customers/getCustomers') ?>",
            data: "data",
            dataType: "json",
            success: function (data) {
                $('#customers_sms').select2("destroy").empty().select2({closeOnSelect: false});
                $.each(data.aaData, function () {
                    $("<option />", {value: this['4'] + ':' + this['3'], text: this['4'] + '/' + this['3'] + ''}).appendTo($('#customers_sms'));
                });
                $('#customers_sms').select2('val');
                $("#customers_sms option").each(function () {
                    $customer_list = $(this).val();
                });
                
                
                  $('#customers_email').select2("destroy").empty().select2({closeOnSelect: false});
                $.each(data.aaData, function () {
                    $("<option />", {value: this['4'] + ':' + this['3'], text: this['4'] + '/' + this['3'] + ''}).appendTo($('#customers_email'));
                });
                $('#customers_email').select2('val');
                $("#customers_email option").each(function () {
                    $customer_list = $(this).val();
                });
                
                 $('#customers_app_msg').select2("destroy").empty().select2({closeOnSelect: false});
                $.each(data.aaData, function () {
                    $("<option />", {value: this['4'] + ':' + this['3'], text: this['4'] + '/' + this['3'] + ''}).appendTo($('#customers_app_msg'));
                });
                $('#customers_app_msg').select2('val');
                $("#customers_app_msg option").each(function () {
                    $customer_list = $(this).val();
                });

                // WhatsApp customers: reuse same phone/email format
                $('#customers_whatsapp').select2("destroy").empty().select2({closeOnSelect: false});
                $.each(data.aaData, function () {
                    $("<option />", {value: this['4'] + ':' + this['3'], text: this['4'] + '/' + this['3'] + ''}).appendTo($('#customers_whatsapp'));
                });
                $('#customers_whatsapp').select2('val');
                
                
                // Award Points
                 $('#customers_sms_award').select2("destroy").empty().select2({closeOnSelect: false});
                $.each(data.aaData, function () {
                    $("<option />", {value: this['0'],text: this['4'] + '/' + this['2'] + ''}).appendTo($('#customers_sms_award'));
                });
                $('#customers_sms_award').select2('val');
                $("#customers_sms_award option").each(function () {
                    $customer_list = $(this).val();
                });
                // End Award Points
                
                
            },
            error: function () {
            }
        });
	
        
        /*---------------- Customer Autopopulate for Email--------------------*/
       /* $.ajax({
            type: "get",
            async: false,
            url: "<?php // site_url('customers/getCustomers') ?>",
            data: "data",
            dataType: "json",
            success: function (data) {
                $('#customers_email').select2("destroy").empty().select2({closeOnSelect: false});
                $.each(data.aaData, function () {
                    $("<option />", {value: this['4'] + ':' + this['3'], text: this['4'] + '/' + this['3'] + ''}).appendTo($('#customers_email'));
                });
                $('#customers_email').select2('val');
                $("#customers_email option").each(function () {
                    $customer_list = $(this).val();
                });
            },
            error: function () {
            }
        }); */
	
        
        /*---------------- Customer Autopopulate for Appmsg--------------------*/
       /* $.ajax({
            type: "get",
            async: false,
            url: "<?php // site_url('customers/getCustomers') ?>",
            data: "data",
            dataType: "json",
            success: function (data) {
                $('#customers_app_msg').select2("destroy").empty().select2({closeOnSelect: false});
                $.each(data.aaData, function () {
                    $("<option />", {value: this['4'] + ':' + this['3'], text: this['4'] + '/' + this['3'] + ''}).appendTo($('#customers_app_msg'));
                });
                $('#customers_app_msg').select2('val');
                $("#customers_app_msg option").each(function () {
                    $customer_list = $(this).val();
                });
            },
            error: function () {
            }
        });
		*/
	$(document).on('click', '.sms_notallow', function (e) {
            e.preventDefault();
            
        });
     
         
   
        $(document).on('click', 'a.group_button', function () {
            $('a.group_button').removeClass('active');
            $(this).addClass('active');
        });
     
        $(document).on('change', '#customers', function () {
            $('a.group_button').removeClass('active');
            $("#group_id").val('');
        });

        /*---------------------------- Set Group Id in hidden (SMS group tab) ----------------------------*/
        $(document).on('click', '#group-sms a.group_button', function () {
            var gid = $(this).attr('data-value');
            if (gid > 0) {
                $('#sms_group_id').val(gid);
            }
        });

        /*---------------------------- Set Group Id in hidden (WhatsApp group tab) ----------------------------*/
        $(document).on('click', '#group-whatsapp a.group_button', function () {
            var gid2 = $(this).attr('data-value');
            if (gid2 > 0) {
                $('#whatsapp_group_id').val(gid2);
            }
        });
    
    
        $(document).on('click', '#select2-choices', function () {
            $('a.group_button').removeClass('active');
            $('#group_id').removeVal();
        });
        
        /* --------------------------- Set SMS from  template  ------------------------*/
        $(document).on('click', '.tempalte_type_1', function () {
            var smsId = getSmsTabIndex(); 
            $('#dlt_te_id').val($(this).attr('data-dltteid'));
            $('#dlt_te_id_group').val($(this).attr('data-dltteid'));
            setTemplateValue($(this).attr('data-value'),$('#'+smsId+' .sms_body'),'','','');
        });
        
         /* --------------------------- Set Email from  template  ------------------------*/
        
        $(document).on('click', '.tempalte_type_2', function () {
            var emailId = getEmailTabIndex();  
            
            setTemplateValue($(this).attr('data-value'),$('#'+emailId +' .email_body'),$('#'+emailId +' #subject'),$('#'+emailId +' .attachment_template'),$('#'+emailId +' .attachment_wrapper'));
        });
        
        /* --------------------------- Set Application Msg from  template  ------------------------*/
        $(document).on('click', '.tempalte_type_3', function () {
            var appmsgId = getAppmsgTabIndex(); 
//            $('#'+appmsgId+' .appmsg_body').attr('value',$(this).attr('data-message'))
          
            setTemplateValue($(this).attr('data-value'),$('#'+appmsgId +' .appmsg_body'),$('#'+appmsgId +' #subject'),$('#'+appmsgId +' .attachment_template'),$('#'+appmsgId +' .attachment_wrapper'));
        });

        /* --------------------------- Reuse application templates for WhatsApp text ------------------------*/
        $(document).on('click', '.whatsapp_template .tempalte_type_3', function () {
            var waId = getWhatsappTabIndex();
            setTemplateValue($(this).attr('data-value'),$('#'+waId +' .wa_body'),'','','');
        });
        
        /* --------------------------- Email Priview ------------------------*/
        $(document).on('click', '.btn_email_preview', function () {
            var emailId = getEmailTabIndex();
            eBody = $('#'+emailId+' .email_body').redactor('get');
            $('#emailPreview .modal-body').html(eBody);
            $('#emailPreview').modal('show'); 
         
        });
      
       $(document).on('click', '#sms_log_invoke', function () {
           
            
            $.ajax({
                type: "GET",
                async: false,
                url: "<?= site_url('smsdashboard/sms_list') ?>",
                dataType: "json",
                success: function (res) {
                	console.log(data_tbl );
                	if(data_tbl){
                		data_tbl.fnDestroy();
                	}
	                $('#sms_log_tbody').empty();
			$('#sms_log_tbody').html(res['data']) ;
			data_tbl = $('#sms_log_table').DataTable({
			         aaSorting : [[ 0, "desc" ]] 
			    });
                },
                error: function () {
                }
            }); 
        });

        /* --------------------------- WhatsApp single submit ------------------------*/
        $("#whatsapp-single").submit(function (event) {
            event.preventDefault();
            var cust_list = $.trim($('#customers_whatsapp').select2('val'));
            var wa_content = $.trim($('#wa_body_single').val());

            if (cust_list == '') {
                alert('Please select customer');
                return false;
            }
            if (wa_content == '') {
                alert('WhatsApp content is empty');
                return false;
            }

            $('#hiddencust_whatsapp').val(cust_list);
            $('#whatsapp-loader').html('<div class="alert alert-success"><i class="fa fa-refresh  fa-spin fa-fw"></i><span class="">Loading...</span></div>');

            $.ajax({
                type: "POST",
                url: "<?= site_url('smsdashboard/addSingleWhatsapp') ?>",
                data: new FormData(this),
                processData: false,
                contentType: false,
                dataType: "json",
                success: function (data) {
                    $('#whatsapp-loader').html('');
                    if (typeof data.error !== 'undefined') {
                        alert(data.error);
                    } else if (typeof data.success !== 'undefined') {
                        alert(data.success);
                        $('#wa_body_single').val('');
                        $('#customers_whatsapp').select2('val', '');
                    }
                },
                error: function () {
                    $('#whatsapp-loader').html('');
                    alert('Error while sending WhatsApp message');
                }
            });
        });

        /* --------------------------- WhatsApp group submit ------------------------*/
        $("#whatsapp-group").submit(function (event) {
            event.preventDefault();
            var gid = $('#whatsapp_group_id').val();
            var wa_content = $.trim($('#wa_body_group').val());

            if (gid == '') {
                alert('Please select group');
                return false;
            }
            if (wa_content == '') {
                alert('WhatsApp content is empty');
                return false;
            }

            $('#whatsapp-loader').html('<div class="alert alert-success"><i class="fa fa-refresh  fa-spin fa-fw"></i><span class="">Loading...</span></div>');

            $.ajax({
                type: "POST",
                url: "<?= site_url('smsdashboard/addGroupWhatsapp') ?>",
                data: new FormData(this),
                processData: false,
                contentType: false,
                dataType: "json",
                success: function (data) {
                    $('#whatsapp-loader').html('');
                    if (typeof data.error !== 'undefined') {
                        alert(data.error);
                    } else if (typeof data.success !== 'undefined') {
                        alert(data.success);
                        $('#wa_body_group').val('');
                        $('a.group_button').removeClass('active');
                        $('#whatsapp_group_id').val('');
                    }
                },
                error: function () {
                    $('#whatsapp-loader').html('');
                    alert('Error while sending WhatsApp message');
                }
            });
        });
    });
    <?php if(!is_array($groupList) || count($groupList)==0):?> 
        $('.group_submit_button').addClass("dis_submit");
    <?php endif;?>    
</script>

(function( $ ) {
  'use strict';
    jQuery(function($)
    {

      /*jQuery.ajax({
        url : doctorVerifiedParams.ajax_url,
        type : 'POST',
        data : {
         'action': 'doctor_verified_check_user_email',
         'txtEmailID' : $( "#txtEmailID" ).val()
        },
        //data:$("#domainForm").serialize(),
        success : function( data ) {
          //console.log(data);
          $("#display-form-result").html(data);

        },
        error:function (){}
      });*/    

      //console.log(doctorVerifiedParams.ajax_url);

     /* jQuery.validator.addMethod("uniqueEmail", function(value, element) {
          var response;
          jQuery.ajax({
              type: "POST",
              url: doctorVerifiedParams.ajax_url,
              data:{
                'action': 'doctor_verified_check_user_email',
                'txtEmailID': $("#txtEmailID").val()
              },
              //async:false,
              success:function(data){
                  response = data;
                  console.log(response);
              }
          });
        
        if(response.toString().trim() == "true")
          {
              return true;
          }
          else
          {
              return false;
          }

      }, "Email is Already Taken");*/


  


$("#applyform .actions > ul > li a[href$='#next']").hide();
//$('#applyform .do-you-have-a-coupon').hide();
//VALIDATE USER EMAIL
$.validator.addMethod("validateUserEmail", function(value, element)
{
    console.log(value);
    var inputElem = $('#txtEmailID'),
        //data = { 'emails' : inputElem.val() },
        //data = { 'action': 'doctor_verified_check_user_email', 'emails' : inputElem.val() },
        eReport = ''; //error report

    $.ajax(
    {
        type: "POST",
        //url: 'http://localhost/wpplugins/wp-content/plugins/doctor-verified/check-email.php', //doctorVerifiedParams.ajax_url,
        url: 'http://www.nutritionist-verified.com/wp-content/plugins/doctor-verified/check-email.php',
        //url: 'http://localhost/wpplugins/web-seal/check-email.php', //doctorVerifiedParams.ajax_url,
        //url: 'http://www.nutritionist-verified.com/web-seal/check-email.php', //doctorVerifiedParams.ajax_url,
        //dataType: "json",
        data: { 'emails' : inputElem.val() },
        success: function(data)
        { 
          //console.log(inputElem.val());
          console.log(data);
            if (data !== 'true')
            {
              return 'This email address is already registered.';
            }
            else
            {
               return true;
            }
        },
        error: function(xhr, textStatus, errorThrown)
        {
            alert('ajax loading error... ... '+url + query);
            return false;
        }
    });

}, '');

//$('#txtEmailID').rules("add", { "validateUserEmail" : true} );


      /*jQuery.validator.addMethod("valDomain", function(value, element) {
        // allow any non-whitespace characters as the host part
        return this.optional( element ) || /(.*?)[^w{3}.]([a-zA-Z0-9]([a-zA-Z0-9-]{0,65}[a-zA-Z0-9])?.)+[a-zA-Z]{2,6}/igm.test( value );
      }, 'Please enter a valid Domain.');*/      

      jQuery.validator.addMethod("checkurl", function(value, element) {
        // now check if valid url
        //return /^(www\.)[A-Za-z0-9_-]+\.+[A-Za-z0-9.\/%&=\?_:;-]+$/.test(value);
        return /(.*?)[^w{3}.]([a-zA-Z0-9]([a-zA-Z0-9-]{0,65}[a-zA-Z0-9])?.)+[a-zA-Z]{2,6}/igm.test(value);
        }, "Please enter a valid URL."
      );

      // a custom method making the default value for companyurl ("http://") invalid, without displaying the "invalid url" message
      jQuery.validator.addMethod("defaultInvalid", function(value, element) {
        return value != element.defaultValue;
      }, "");

      var form = $("#applyform").show();

      form.steps({
          headerTag: "h3",
          bodyTag: "fieldset",
          transitionEffect: "slideLeft",
          //enableFinishButton: true,
          onStepChanging: function (event, currentIndex, newIndex)
          {

             console.log('onStepChanging' + currentIndex);
              // Allways allow previous action even if the current form is not valid!
              if (currentIndex > newIndex)
              {
                  return true;
              }

              // Forbid next action on "Warning" step if the user is to young
              /*if (newIndex === 3 && Number($("#age-2").val()) < 18)
              {
                  return false;
              }*/

              // Needed in some cases if the user went back (clean up)
              if (currentIndex < newIndex)
              {
                  //$('#applyform .do-you-have-a-coupon').hide();
                  // To remove error styles
                  form.find(".body:eq(" + newIndex + ") label.error").remove();
                  form.find(".body:eq(" + newIndex + ") .error").removeClass("error");
              }

              form.validate().settings.ignore = ":disabled,:hidden";
              return form.valid();
          },
          onStepChanged: function (event, currentIndex, priorIndex)
          {

              console.log('onStepChanged' + currentIndex);
              // Used to skip the "Warning" step if the user is old enough.
              //if (currentIndex === 2 && Number($("#age-2").val()) >= 18)

              /*if (currentIndex === 2)  
              {
                  form.steps("next");
              }

              // Used to skip the "Warning" step if the user is old enough and wants to the previous step.
              if (currentIndex === 2 && priorIndex === 3)
              {
                  form.steps("previous");
              }*/

              if (currentIndex === 1)
              {
                  //var finishBtn = $(".wizard > .actions ul > li > a").last().hide().attr("href");
                  //if ('#finish' === finishBtn) {}
                 //window.location.hash='step2';
                  //console.log(window.location.hash='step2');
                  $("#stripe_script_container #customButton").hide();
                  $(".wizard > .actions").show();
                  $(".wizard > .actions ul > li > a[href^='#previous']").show();
                  $(".wizard > .actions ul > li > a").last().hide().attr("href");//// Hide "Apply" Button
                  $('#coupon_block').hide();
                  $('#yes_coupon').prop('checked', false);
                  $('#no_coupon').prop('checked', false);
                  $('#coupon1').val("");
                  $('#coupon2').val("");

                  $("#setup_price").text('$99.95');
                  $("#monthly_cost").text('$49.95*');
                  $("#total_setup_cost").text('$99.95');                   

              }             

          },
          onFinishing: function (event, currentIndex)
          {
              form.validate().settings.ignore = ":disabled";
              return form.valid();
          },
          onFinished: function (event, currentIndex)
          {
            //alert("Submitted!");
            form.get(0).submit();
            //form.submit();
            //console.log(currentIndex);

            //console.log(doctorVerifiedParams.ajax_url);

            //console.log(jQuery("#coupon1").val());
            //console.log(jQuery("#coupon2").val());


            /*jQuery.ajax({
              url : doctorVerifiedParams.ajax_url,
              type : 'POST',
              dataType: 'json',
              data : {
                action : 'doctor_verified_apply',
                coupon1 : 'test1', //jQuery("#coupon1").val(),
                coupon2 : 'test2' //jQuery("#coupon2").val()
              },
              //data:$("#domainForm").serialize(),
              complete: function( xhr, status ){
                  console.log("Request complete: " + status);
              },
              error: function( xhr, status, errorThrown ){
                  console.log("Request failed: " + status);
              },
              success: function( data, status, xhr ){
                  console.log("Request success: " + data);
                  // change the html
                 $("#message").html(data);
              }
            });*/

            /*var data = {
                action: 'doctor_verified_apply',
                'coupon1' : jQuery("#coupon1").val(),
                'coupon2' : jQuery("#coupon2").val()
            };
            jQuery.post(doctorVerifiedParams.ajax_url, data, function(response) {
                jQuery("#message").html(response);
            });*/ 

          },
          /* Labels */
          labels: {
              cancel: "Cancel",
              current: "current step:",
              pagination: "Pagination",
              finish: "Complete your Order!",
              next: "Continue",
              previous: "Previous",
              loading: "Loading ..."
          } 
      }).validate({
          errorPlacement: function errorPlacement(error, element) { element.before(error); },
          rules: {
              /*confirm: {
                  equalTo: "#password-2"
              }*/

              txtDomain: {
                required: true,
                checkurl: true,
                defaultInvalid: true,
                //url: true
              },
              txtEmailID: {
                required: true,
                email: true,
                //uniqueEmail:true
                //validateUserEmail:true
                /*remote: {
                        url: 'http://localhost/wpplugins/web-seal/check-email.php', //'http://localhost/wpplugins/wp-content/plugins/doctor-verified/check-email.php', //doctorVerifiedParams.ajax_url, //'http://localhost/wpplugins/wp-admin/admin-ajax.php', 
                        type: "post",
                        dataType: "json",
                        data: {
                           'txtEmailID': function() {
                                return jQuery( "#txtEmailID" ).val();
                            },

                           'txtEmailID': jQuery( "#txtEmailID" ).val(),
                           'action': 'doctor_verified_check_user_email'

                        }
                }*/
                "remote":
                {                  
                  //url: 'http://localhost/wpplugins/web-seal/check-email.php',
                  //url: 'http://www.nutritionist-verified.com/web-seal/check-email.php',
                  //url: 'http://localhost/wpplugins/wp-content/plugins/doctor-verified/check-email.php',
                  url: 'http://www.nutritionist-verified.com/wp-content/plugins/doctor-verified/check-email.php',

                  type: "post",
                  data:
                  {
                      email: function()
                      {
                          return $('#applyform :input[name="txtEmailID"]').val();
                      }
                  }
                }
                
              },
              txtNewPassword: {
                required: true,
                minlength: 6,
                maxlength: 25
              }
          }
      });


      $('#yes_coupon').change(function(){
         if($(this).is(":checked")){
          $('#coupon_block').show();
          //$("#stripe_script_container #customButton").show();
          $("#stripe_script_container #customButton").hide();
          $(".wizard > .actions").show();
          $(".wizard > .actions ul > li > a[href^='#previous']").show();
          $(".wizard > .actions ul > li > a").last().hide().attr("href");//// Hide "Apply" Button

          $('#coupon1').val("");
          $('#coupon2').val("");    

         } else {
            $('#coupon_block').hide();
            $("#stripe_script_container #customButton").show();
            $(".wizard > .actions").show();
            $(".wizard > .actions ul > li > a[href^='#previous']").show();
            $(".wizard > .actions ul > li > a").last().hide().attr("href");//// Hide "Apply" Button    

            $('#coupon1').val("");
            $('#coupon2').val("");                    
         }
      });
      
      $('#no_coupon').change(function(){
         if($(this).is(":checked")){

          //location.href="/web/20160430033815/https://doctor-certified.com/apply.php?coupon=no";
          $('#coupon_block').hide(); 
          
          $("#stripe_script_container #customButton").show();
          $(".wizard > .actions").show();
          $(".wizard > .actions ul > li > a").last().hide().attr("href");//// Hide "Apply" Button

          $('#coupon1').val("");
          $('#coupon2').val("");
         }
      });


      $(document).on("click", '#check_coupon_validity', function(){

        //discount formula $amount = $amount - ( $amount * ( $coupon->percent_off / 100 ) );
        
        var coupon1 = $('#coupon1').val(); 
        var coupon2 = $('#coupon2').val();

        var setupPrice = 0, monthlyCost = 0, totalSetupCost = 0;

        if(coupon1 != '' || coupon2 != '') {
        //console.log('check_coupon_validity');

          //coupon1.toLowerCase();
          //coupon2.toLowerCase();

          if(coupon1.trim().toLowerCase() == doctorVerifiedParams.one_time_setup || 
             coupon2.trim().toLowerCase() == doctorVerifiedParams.monthly_service) {  

            alert('Your code is valid!');          
            
             //console.log('valid coupon code');            
            $("#stripe_script_container #customButton").show();            
          
            if(coupon1.trim().toLowerCase() == doctorVerifiedParams.one_time_setup) {
              $("#setup_price").text('$0.00');
              setupPrice = 0.00;
              $("#total_setup_cost").text('$'+setupPrice.toFixed(2));          
            } else {
              $("#setup_price").text('$99.95');
              setupPrice = 99.95;
              $("#total_setup_cost").text('$'+setupPrice.toFixed(2));               
            }

            if(coupon2.trim().toLowerCase() == doctorVerifiedParams.monthly_service) {
              //console.log(price_discount(49.95));
              $("#monthly_cost").text('$29.95*');
              monthlyCost = 29.95;
              //$("#monthly_cost").text(price_discount(49.95)+'*');
              //monthlyCost = price_discount(49.95);
              
            } else {
              $("#monthly_cost").text('$49.95*');
              monthlyCost = 49.95;            
            }  

            totalSetupCost = setupPrice;

            $("#total_setup_cost").text('$'+totalSetupCost.toFixed(2));      

          } else {

            alert('Your code is not valid!');
            $('#coupon1').val(""); 
            $('#coupon2').val("");
            $("#stripe_script_container #customButton").hide();
          }

        } else { 
          alert('Enter at least one Coupon.');
          $("#setup_price").text('$99.95');
          $("#monthly_cost").text('$49.95*');
          $("#total_setup_cost").text('$99.95'); 
          setupPrice = 99.95;          
        }

        //jQuery('form').append(jQuery('<input type="hidden" name="amount">').val(setupPrice));
        //$("#amount").val(setupPrice);

      });      


      /*function price_discount(amount) {

        var amount, couponPercentOff; 

        return amount - ( amount * ( 20 / 100 ) ); //20 percent discount

      }*/


      /*$(document).on("click", '#apply_coupon', function(){
        
       if($('#coupon1').val() != '' || $('#coupon2').val() != '') {
        //setNewCost($('#coupon1').val(), $('#coupon2').val());

       }
       //
      else alert('Enter at least one Coupon.');

      });*/   

      //console.log(doctorVerifiedParams.email);

      //custom Checkout integration
      var handler = StripeCheckout.configure({
          key: doctorVerifiedParams.publishable_key,
          image: '/img/documentation/checkout/marketplace.png',
          locale: 'auto',
          token: function(token) {
            // You can access the token ID with `token.id`.
            // Get the token ID to your server-side code for use.

            var token = token.id;

            if( typeof token != "undefined" ) {
              $(".wizard > .actions ul > li > a[href^='#previous']").hide();
              $(".wizard > .actions ul > li > a").last().show().attr("href");
            } else {

              console.log('test');

            }

            // Insert the token ID into the form so it gets submitted to the server:
            jQuery('form').append(jQuery('<input type="hidden" name="stripeToken">').val(token));  


            var coupon1 = $('#coupon1').val(); 
            var coupon2 = $('#coupon2').val();

            var setupPrice = 0, monthlyCost = 0, totalSetupCost = 0;

            if(coupon1 != '' || coupon2 != '') {

              if(coupon1.trim().toLowerCase() == doctorVerifiedParams.one_time_setup || 
                 coupon2.trim().toLowerCase() == doctorVerifiedParams.monthly_service) {  

                if(coupon1.trim().toLowerCase() == doctorVerifiedParams.one_time_setup) {
                  setupPrice = 0.00;          
                } else {
                  setupPrice = 99.95;              
                }

                if(coupon2.trim().toLowerCase() == doctorVerifiedParams.monthly_service) {
                  monthlyCost = 29.95;                  
                } else {
                  monthlyCost = 49.95;            
                }  

                totalSetupCost = setupPrice;

              } else {}

            } else {               
              setupPrice = 99.95;   
              monthlyCost = 49.95;   
              totalSetupCost = setupPrice;      
            }

            //jQuery('form').append(jQuery('<input type="hidden" name="amount">').val(setupPrice));
            //$("#amount").val(setupPrice);

            $("#one_time_setup_fee").val(setupPrice.toFixed(2));
            $("#monthly_service_fee").val(monthlyCost.toFixed(2));
            $("#total_charge_today").val(totalSetupCost.toFixed(2));

          }
        });

        //https://stripe.com/docs/checkout#integration-custom
        $('#customButton').on('click', function(e) {

            var coupon1 = $('#coupon1').val(); 
            var coupon2 = $('#coupon2').val();

            var setupPrice = 0, monthlyCost = 0, totalSetupCost = 0;

            if(coupon1 != '' || coupon2 != '') {

              if(coupon1.trim().toLowerCase() == doctorVerifiedParams.one_time_setup || 
                 coupon2.trim().toLowerCase() == doctorVerifiedParams.monthly_service) {  

                if(coupon1.trim().toLowerCase() == doctorVerifiedParams.one_time_setup) {
                  setupPrice = 0.00;          
                } else {
                  setupPrice = 99.95;              
                }

                if(coupon2.trim().toLowerCase() == doctorVerifiedParams.monthly_service) {
                  monthlyCost = 29.95;                  
                } else {
                  monthlyCost = 49.95;            
                }  

                totalSetupCost = setupPrice;

              } else {}

            } else {               
              setupPrice = 99.95;   
              monthlyCost = 49.95;   
              totalSetupCost = setupPrice;          
            }
          
          // Open Checkout with further options:
          handler.open({
            name: 'Doctor Certified&trade;',
            //description: 'One Time Setup Fee - $99.95',
            description: 'One Time Setup Fee - $' + setupPrice.toFixed(2),
            image: 'http://www.nutritionist-verified.com/wp-content/plugins/doctor-verified/includes/img/marketplace.png',
            //amount: 9995,
            amount: setupPrice.toFixed(2) * 100,
            email: $( "#txtEmailID" ).val()
          });
          e.preventDefault();
        });

        // Close Checkout on page navigation:
        $(window).on('popstate', function() {
          handler.close();
        });

    });


    //MEMBER AREA
    jQuery( document ).on( 'click', '#btnSaveDomain', function() {
    //jQuery( '#domainForm' ).on( 'submit', '#btnSaveDomain', function() {    

    jQuery("#display-form-result").html('');

    if ( jQuery.trim(jQuery("#website1").val()) != '' || 
       jQuery.trim(jQuery("#website2").val()) != '' || 
       jQuery.trim(jQuery("#website3").val()) != '' || 
       jQuery.trim(jQuery("#website4").val()) != '' || 
       jQuery.trim(jQuery("#website5").val()) != '' ) {
     
      if( (jQuery.trim(jQuery("#website1").val()) != '' && isValidDomain(jQuery("#website1").val())) || 
        (jQuery.trim(jQuery("#website2").val()) != '' && isValidDomain(jQuery("#website2").val())) || 
        (jQuery.trim(jQuery("#website3").val()) != '' && isValidDomain(jQuery("#website3").val())) ||
        (jQuery.trim(jQuery("#website4").val()) != '' && isValidDomain(jQuery("#website4").val())) ||
        (jQuery.trim(jQuery("#website5").val()) != '' && isValidDomain(jQuery("#website5").val())) ) {

        jQuery.ajax({
          url : doctorVerifiedParams.ajax_url,
          type : 'POST',
          data : {
            'action' : 'doctor_verified_send_mail',
            'website1' : isValidDomain(jQuery("#website1").val()) ? jQuery("#website1").val() : '',
            'website2' : isValidDomain(jQuery("#website2").val()) ? jQuery("#website2").val() : '',
            'website3' : isValidDomain(jQuery("#website3").val()) ? jQuery("#website3").val() : '',
            'website4' : isValidDomain(jQuery("#website4").val()) ? jQuery("#website4").val() : '',
            'website5' : isValidDomain(jQuery("#website5").val()) ? jQuery("#website5").val() : ''
          },
          //data:$("#domainForm").serialize(),
          success : function( data ) {
            //console.log(data);
            $("#display-form-result").html(data);

          },
          error:function (){}
        });       



      } else {

        $('#display-form-result').html('Domain should be valid.');  

      }

    } else {

      $('#display-form-result').html('At least one domain is required.');  

    }

    })   

    function isValidDomain(value){
     var regex = /(.*?)[^w{3}.]([a-zA-Z0-9]([a-zA-Z0-9-]{0,65}[a-zA-Z0-9])?.)+[a-zA-Z]{2,6}/igm;
     
     //var regex = /^(?!:\/\/)([a-zA-Z0-9-]+\.){0,5}[a-zA-Z0-9-][a-zA-Z0-9-]+\.[a-zA-Z]{2,64}?$/gi;

     if(regex.test(value)){
      return true;   
     }
     else return false;
    }





    jQuery(function($) {
        jQuery('#invoiceTable').DataTable();
    } );


})( jQuery );


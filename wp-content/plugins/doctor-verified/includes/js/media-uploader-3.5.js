/*
 * Adapted from: http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/
 */
jQuery(document).ready(function($){


// Uploading files
var file_frame;
var buttonClass;

  $('.media-uploader-1, .media-uploader-2, .media-uploader-3').on('click', function( event ){

    event.preventDefault();

    buttonClass = $(this).attr("id");

    console.log(buttonClass);

    // If the media frame already exists, reopen it.
    if ( file_frame ) {
      file_frame.open();
      return;
    }

    // Create the media frame.
    file_frame = wp.media.frames.file_frame = wp.media({
      title: jQuery( this ).data( 'uploader_title' ),
      button: {
        text: jQuery( this ).data( 'uploader_button_text' ),
      },
      multiple: false  // Set to true to allow multiple files to be selected
    });

    // When an image is selected, run a callback.
    file_frame.on( 'select', function() {
      // We set multiple to false so only get one image from the uploader
      attachment = file_frame.state().get('selection').first().toJSON();
      // Do something with attachment.id and/or attachment.url here
      //console.log(attachment);
    

      if (buttonClass === 'media-uploader-1') {
        jQuery('#doctor_photo').attr('value', attachment.url);
      }

      if (buttonClass === 'media-uploader-2') {
        jQuery('#reviewer_proof_of_certificate_url').attr('value', attachment.url);
      }

      if (buttonClass === 'media-uploader-3') {
        jQuery('#reviewer_photo_url').attr('value', attachment.url);
      }

    });

    // Finally, open the modal
    file_frame.open();
  });
 
});
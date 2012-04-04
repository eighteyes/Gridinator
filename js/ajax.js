function resetEdit() {
    //console.log("reset edit");
    $('.edit_details').unbind('click');
    $('.edit_details').text('Edit');
    $('.save_details').hide();

    $('#details_header_input, #details_input').hide();
    $('#details_content, #details h1').show();
}

function activateUploadLink() {
	
	$('input[type=file]').each(function() {
		var img = $(this).attr('data-imageurl');
		console.log('image exists ?', img, $(this));

			$('<img>').attr('width',100).attr('height',100).addClass('tn').attr('src', img).insertBefore($(this));
		
	});

    $('.upload_img').click(function(e) {
        e.preventDefault();
        var originalInput = $(this).prev('input');
        var originalButton = $(this);
        var file = $(this).parent().find('input[type=file]');

		var tn = originalInput.prev('.tn');

		originalButton.children('.spinner').fadeIn();
        function uploadError(msg) {
            this.message = msg;
        }

        function checkForImage(file) {
            if (!file.type.has('image')) {
                error = new uploadError("Must Upload an Image");
                throw error;
            }
        }


        if ($(this).parent().attr('id').has('example')) {
            loc = 'example';
        } else {
            loc = 'explain';
        }

        var data = new FormData();

        $.each(file[0].files, function(i, file) {
            try {
                checkForImage(file);
            } catch(e) {
                console.log('error', e);
                alert(e.message);
            }
            data.append('file-' + i, file);
            console.log(file);
            type = file.fileName.substr(0, -3);
        });

        data.append('action', 'upload_image');
        data.append('location', loc);

        //console.log('data', data);

        $.ajax({
            url: TreeAjax.ajaxurl,
            data: data,
            contentType: false,
            processData: false,
            type: 'POST',

            success: function(data) {
                //console.log(data);
                if (data.substr(-1) == '0') {
					spinner.fadeOut();
                    fileURL = data.substr(0, data.length - 1);
                    //replace and disable
                    originalButton.remove();
                    console.log("Upload Success:" + fileURL);
                    // find the file input, save to value, sent to server on SAVE
                    originalInput.attr('data-imageurl', fileURL).after('<span>Success!</span>');
					tn.attr('src', fileURL);
                }
                //called when successful
            }
        });

    });
}

function setupEditButton() {

    $('.edit_details').click(function(e) {
        e.stopPropagation();
        setupSaveButton();

        $('#details_header_input').select();

        $('.save_details').show();

        //swap textfield
        var str = $('#details_content').hide().text();
        $('#details_input').show().val(str);

        //swap header
        var str = $('#details h1').hide().text();
        $('#details_header_input').val(str).show();

        //reset if click cancel
        $(this).html('Cancel | ');
        $(this).click(function() {
            $('#blackout').click();
        });

        $('#details_input, #details_header_input').click(function(e) {
            e.stopPropagation();
        });
    });
}



function setupSaveButton() {
    // save changes to json object
    
    $('.save_details').unbind('click');
    $('.save_details').click(function() {
        var id = activeId;
        var activeDOMNode = activeNode;
        var dataStorage = activeDOMNode.next('.details_data');
        var name = $('#details #details_header_input').val();

     

        activeDOMNode.text(name);

        //local info
        saveChangestoPageJSON();
        saveNametoGrid();

        var data = {
            action: 'edit_node',
            data: JSON.stringify(dataJSON),
            id: id,
            treeid: treeId
        }
        console.log('formdata', data);

        $.post(TreeAjax.ajaxurl, data, function(response) {
            console.log('AJAX Return', response);

            //logistical details
            resetEdit();
        });
    });
}

function saveChangestoPageJSON() {
    $('#details_input input, #details_input textarea').each(function() {
        key = $(this).attr('name');
        if ($(this).attr('type') == 'file') {
            dataJSON[key] = $(this).attr('data-imageurl') || "";
        } else {
            dataJSON[key] = escape($(this).val());
        }
        //console.log(key, dataJSON[key]);
    });
    console.log('DataJSON', dataJSON);

	//write to page JSON
    dataNode.attr('data-json', JSON.stringify(dataJSON));
}

function saveNametoGrid() {
    activeNode.html(unescape(dataJSON.name));
}
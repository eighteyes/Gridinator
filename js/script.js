jQuery(document).ready(function() {
    activeId = 0;
    treeId = 0;
    activeNode = {};
    dataJSON = {};
    dataNode = {};
	spinner = $('.spinner');

    $(".activateNode").click(function(e) {
        activeId = $(this).attr('data-id');
        treeId = $(this).parents('.grid').attr('tree-id');
        activeNode = $(this);

        clearContent();
        showDetails($(this));

        setupEditButton();
    });

    function clearContent() {
        $('#details_content, #details_input').html("");
    }

    function showDetails(target) {
        $("#blackout").fadeIn(100).click(function() {
            $(this).hide();
            resetEdit();
        });
        dataNode = target.next('.details_data');

        //read from pageJSON
        dataJSON = $.parseJSON(dataNode.attr('data-json'));
        console.log('Page JSON: ', dataJSON);

        for (key in dataJSON) {
            dataJSON[key] = unescape(dataJSON[key] || "");
        }
        buildContentHTML();
    }

    function makeLinks(str) {
        var def = str;
        var linesArray = def.lines();
        var html = "";
        var refs = "";
        var reg = RegExp("((http\://|https\://|ftp\://)|(www.))+(([a-zA-Z0-9\.-]+\.[a-zA-Z]{2,4})|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(/[a-zA-Z0-9%:/-_\?\.'~]*)?");

        for (var j in linesArray) {
            if (linesArray[j].has(reg)) {
                // has a link?
                str = linesArray[j];
                html += "<p><a href='" + str + "'>" + str + "</a></p>";
            } else {
                //make returns real
                html += "<p>" + linesArray[j] + "</p>";
            }
        }

        return html;
    }

    function buildContentHTML() {

        //make where everything is supposed to go
        var elems = [];
        var example_div = document.createElement('div');
        example_div.id = 'example_wrap';
        var explain_div = document.createElement('div');
        explain_div.id = 'explain_wrap';
        var definition_div = document.createElement('div');
        definition_div.id = 'definition_wrap';
        var sources_div = document.createElement('div');
        sources_div.id = 'sources_wrap';
        var example_div_input = document.createElement('div');
        example_div_input.id = 'example_wrap_input';
        var explain_div_input = document.createElement('div');
        explain_div_input.id = 'explain_wrap_input';
        var definition_div_input = document.createElement('div');
        definition_div_input.id = 'definition_wrap_input';
        var sources_div_input = document.createElement('div');
        sources_div_input.id = 'sources_wrap_input';

        // create display content
        for (var prop in dataJSON) {
            if (prop.has('picurl')) {
                //images 
                if (elems[prop] != "") {
                    elems[prop] = document.createElement('img');
                    elems[prop].id = prop;
                    elems[prop].src = dataJSON[prop];
                }

            } else if (prop.has('href')) {

            } else {
                //everything else
                elems[prop] = document.createElement('div');
                elems[prop].id = prop;
                elems[prop].innerHTML = dataJSON[prop];
            }

            //class processing
            if (prop.has('credit')) {
                elems[prop].className = "credit";
            }

            //put elements in div wrappers
            if (prop.has('example')) {
                $(example_div).append(elems[prop]);
            } else if (prop.has('explain')) {
                $(explain_div).append(elems[prop]);
            } else if (prop.has('definition')) {
                $(definition_div).append('<h1>Definition</h1>').append(makeLinks($(elems[prop]).html()));

            } else if (prop.has('href')) {

            } else if (prop.has('ref')) {
                $(sources_div).append('<h1>References</h1>').append(makeLinks($(elems[prop]).html()));
            } else {
                //for name
                $(elems[prop]).appendTo('#details_content');
            }
        }

        addhtml = $(definition_div).add($(example_div)).add($(sources_div)).add($(explain_div));
        addhtml.appendTo('#details_content');

        elems = [];

        //create input content - todo: enable upload
        for (var prop in dataJSON) {

            //make different elements
            if ((prop.has('definition')) || prop.has('ref')) {
                elems[prop] = document.createElement('textarea');
            } else if (prop.has('picurl')) {
                var submit = document.createElement('a');
                submit.classList.add('upload_img');
                submit.innerHTML = "Upload";
                submit.name = "imageFile";

                // image upload form
                elems[prop] = document.createElement('input');
                elems[prop].type = 'file';
                elems[prop].name = prop;
                $(elems[prop]).attr('data-imageurl', dataJSON[prop]);

                $(submit).addClass('imageUpload').after('<p>Images will be resized to 425x250</p>');
				spinner.clone().appendTo($(submit));
                elems[prop] = $(elems[prop]).after($(submit));
            } else {
                elems[prop] = document.createElement('input');
            }

            //flush out element
            elems[prop].name = prop;
            elems[prop].id = prop + "_input";
            elems[prop].value = dataJSON[prop];

            var label = document.createElement('label');
            label.innerHTML = prop;

            //what gets added to the dom
            var add = $(elems[prop]).before($(label));

            //put inputs / labels in div wrappers
            if (prop.has('example')) {
                $(example_div_input).append(add);
            } else if (prop.has('explain')) {
                $(explain_div_input).append(add);
            } else if (prop.has('definition')) {
                $(definition_div_input).append('<h1>Definition</h1>').append(add);
            } else if ((prop.has('href')) || (prop.has('link'))) {
                //obs
            } else if ((prop.has('ref'))) {
                $(sources_div_input).append('<h1>References</h1>').append(add);
            } else {
                $(add).appendTo('#details_input');
            }

        }

        html = $(definition_div_input).add($(example_div_input)).add($(sources_div_input)).add($(explain_div_input));
        html.appendTo('#details_input');

        activateUploadLink();

    }

    //highlight path
});
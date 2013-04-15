function clearUpload(id)
{
    var upload = document.getElementsByName(id)[0];
    upload.value='';

    var upload2 = upload.cloneNode(false);
    upload2.onchange= upload.onchange;
    upload.parentNode.replaceChild(upload2,upload);
}
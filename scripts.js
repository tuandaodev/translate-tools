$( document ).ready(function() {
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        console.log("Selected file: " + fileName);
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
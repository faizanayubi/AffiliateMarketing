$("#tnc").click(function() {
    var checked_status = this.checked;
    if (checked_status == true) {
        $("#register").removeAttr("disabled");
    } else {
        $("#register").attr("disabled", "disabled");
    }
});
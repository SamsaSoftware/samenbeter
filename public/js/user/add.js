$(document).ready(function() {
    $('#adduser').validate({
        rules:{
            email:{
                required:true,
                email:true,
                remote: {
                    url : $('#checkEmail').val(),
                    type : 'post',
                    data : {
                        email: function() {
                            return $('#emailElement').val();
                        },
                        oldEmail: function() {
                            return $('#oldEmail').val();
                        }
                    },
                    success: function(data){
                        if (data) {
                            $('#emailElement').remove('hasError').addClass('hasSuccess');

                        } else {
                            $('#emailElement').remove('hasSuccess').addClass('hasError');
                        }
                    }
                }
            },
            role:{
                required:true
            },
            password:{
                required:true
            },
            name:{
                required:true
            },
            lastname:{
                required:true
            },
            organization:{
                required:true
            },
            deleted:{
                required:true
            }
        },
        messages:
        {
            email:
            {
                remote: 'The email already exists!'
            }
        }
    });
});
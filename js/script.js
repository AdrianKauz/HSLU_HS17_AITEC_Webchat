$(document).ready(function(){
	// Run the init method on document ready:
	chat.init();
});

$('#buttonRegister').click(function(){
    $('#loginMode').val('register');
});

$('#buttonLogin').click(function(){
    $('#loginMode').val('login');
});

$('#chatText').keypress(function(event){
    if(event.which === 13){
        if(!event.shiftKey){
            chat.submit();
        } else {
            if($(this).val().split("\n").length > 2){
                return false;
            }
        }
    }
});

$('#chatText').bind('paste', function(event) {

});

var chat = {
    // data for the current user:
    currentUser : {
        is_admin : false,
        is_connected : true // Just temporary
    },

    activity : {
        loginFormIsWorking : false,
        chatFormIsWorking : false
    },

	// data holds variables for use in the class:
	data : {
		lastID 		: 0,
		noActivity	: 0,
	},

	// Init binds event listeners and sets up timers:
	init : function()
    {
		// Using the defaultText jQuery plugin, included at the bottom:
		$('#name').defaultText('Nickname');
		$('#email').defaultText('Email (Gravatars are Enabled)');
		
		// Converting the #chatLineHolder div into a jScrollPane,
		// and saving the plugin's API in chat.data:
		chat.data.jspAPI = $('#chatLineHolder').jScrollPane({
			verticalDragMinHeight: 12,
			verticalDragMaxHeight: 12
		}).data('jsp');
		
		// We use the working variable to prevent multiple form submissions:
		var working = false;
		
		// Logging a person in the chat:
		$('#loginForm').submit(function(){
			if(chat.activity.loginFormIsWorking){
                return false;
			} else {
                chat.activity.loginFormIsWorking = true;
            }

			// Be careful! -> This solution is a quick and dirty method.
            // If you change the order of the input fields in the html-form, so it changes in the array too!
            var arrInputValues = $(this).serializeArray();

            if(arrInputValues[0].value === "register"){
                chat.registerUser(arrInputValues[1].value, arrInputValues[2].value);
            }

            if(arrInputValues[0].value === "login"){
                chat.loginUser(arrInputValues[1].value, arrInputValues[2].value);
            }

            chat.activity.loginFormIsWorking = false;

            return false;
		});
		
		// Submitting a new chat entry:
		$('#submitForm').submit(function(){
            chat.submit();
		    return false;
		});
		
		// Logging the user out:
		$('a.logoutButton').live('click',function(){
		    chat.logout();
			return false;
		});

		// Checking whether the user is already logged (browser refresh)
		$.chatGET('checkLogged',function(r){
			if(r.logged){
			    if(r.loggedAs.is_admin == 1){
                    chat.currentUser.is_admin = true;
                    $('#chatAdminContainer').show();
                    chat.getUsers();
                }

				chat.login(r.loggedAs.name,r.loggedAs.gravatar);
			} else {
                $('#LoginContainer').fadeIn();
			}
		});
		
		// Self executing timeout functions
		(function getChatsTimeoutFunction(){
			chat.getChats(getChatsTimeoutFunction);
		})();
		
		(function getUsersTimeoutFunction(){
			chat.getUsers(getUsersTimeoutFunction);
		})();

        (function getStatusTimeoutFunction(){
            chat.getStatus(getStatusTimeoutFunction);
        })();


		// Count registered users. If none, set LoggingContainer to admin-mode
        $.chatGET('countUsers',function(r){
        	if(r.total == 0){
                $('#loginFieldTitle').html("Greetings Administrator!");
			}
        });
	},

    submit : function()
    {
        var text = $('#chatText').val();

        if(text.length === 0){
            return false;
        }

        if(chat.activity.chatFormIsWorking) {
            return false;
        } else {
            chat.activity.chatFormIsWorking = true;
        }

        // Assigning a temporary ID to the chat:
        var tempID = 't'+Math.round(Math.random()*1000000),
            params = {
                id			: tempID,
                author		: chat.data.name,
                gravatar	: chat.data.gravatar,
                text		: text.replace(/</g,'&lt;').replace(/>/g,'&gt;')
            };

        // Using our addChatLine method to add the chat
        // to the screen immediately, without waiting for
        // the AJAX request to complete:
        chat.addChatLine($.extend({},params));

        // Using our chatPOST wrapper method to send the chat
        // via a POST AJAX request:
        $.chatPOST('submitChat',$('#submitForm').serialize(),function(r){
            chat.activity.chatFormIsWorking = false;

            $('#chatText').val('');
            $('div.chat-' + tempID).remove();

            params['id'] = r.insertID;
            chat.addChatLine($.extend({},params));
        });


    },

	// The login method hides displays the user's login data and shows the submit form:
	login : function(name,gravatar)
    {
		chat.data.name = name;
		chat.data.gravatar = gravatar;
		$('#chatTopBar').html(chat.render('loginTopBar',chat.data));

        $('#LoginContainer').fadeOut(function(){
            if(chat.currentUser.is_admin){
                $('#chatAdminContainer').fadeIn();
            }

            $('#submitForm').fadeIn();
        	$('#chatText').focus();
        });
	},

    logout : function()
    {
        // chat.currentUser.is_connected = false;

        if(chat.currentUser.is_admin){
            chat.currentUser.is_admin = false;
            $('#chatAdminContainer').fadeOut();
        }

        $('#LoginContainer').fadeIn();
        $('#submitForm').hide();
        $('#chatTopBar > span').remove();

        $.chatPOST('logout');

        return true;
    },
	
	// The render method generates the HTML markup that is needed by the other methods:
	render : function(template,params)
    {
		var arr = [];

		switch(template){
			case 'loginTopBar':
				arr = [
				'<span><img src="',params.gravatar,'" width="23" height="23" />',
				'<span class="name">',params.name,
				'</span><a href="" class="logoutButton rounded">Logout</a></span>'];
			    break;
			case 'chatLine':
				arr = [
					'<div class="chat chat-',params.id,'"><span class="gravatar"><img src="',params.gravatar,
					'" width="23" height="23" onload="this.style.visibility=\'visible\'" />','</span><span class="author">',params.author,
					'</span><span class="time">',params.time,'</span><span class="text">',params.text.split("\n").join("<br>"),'</span></div>'];
			    break;
			case 'user':
			    if(chat.currentUser.is_admin){
                    arr = [
                        '<div class="user" title="Block ',
                        params.name,'"><img class="userIconImage" src="',
                        params.gravatar,
                        '" width="30" height="30" onload="this.style.visibility=\'visible\'" />',
                        '<div class="blockUserButton"><img src="img/block_user.png" width="30" height="30" onclick="chat.blockUser(\'',
                        params.name,
                        '\');"></div>',
                        '</div>'];
                } else {
                    arr = [
                        '<div class="user" title="',
                        params.name,'"><img class="userIconImage" src="',
                        params.gravatar,
                        '" width="30" height="30" onload="this.style.visibility=\'visible\'" />',
                        '</div>'];
                }

			    break;
            case 'blockedUser':
                arr = [
                    '<div class="user" title="Unblock ',
                    params.name,'"><img class="userIconImage" src="',
                    params.gravatar,
                    '" width="30" height="30" onload="this.style.visibility=\'visible\'" />',
                    '<div class="blockUserButton"><img src="img/unblock_user.png" width="30" height="30" onclick="chat.unBlockUser(\'',
                    params.name,
                    '\');"></div>',
                    '</div>'];
                break;
		}
		
		// A single array join is faster than multiple concatenations
		return arr.join('');
	},
	
	// The addChatLine method ads a chat entry to the page
	addChatLine : function(params)
    {
		
		// All times are displayed in the user's timezone
		var d = new Date();
		if(params.time) {
			
			// PHP returns the time in UTC (GMT). We use it to feed the date
			// object and later output it in the user's timezone. JavaScript
			// internally converts it for us.
			d.setUTCHours(params.time.hours,params.time.minutes);
		}
		
		params.time = (d.getHours() < 10 ? '0' : '' ) + d.getHours()+':'+
					  (d.getMinutes() < 10 ? '0':'') + d.getMinutes();
		
		var markup = chat.render('chatLine',params),
			exists = $('#chatLineHolder .chat-'+params.id);

		if(exists.length){
			exists.remove();
		}
		
		if(!chat.data.lastID){
			// If this is the first chat, remove the
			// paragraph saying there aren't any:
			$('#chatLineHolder p').remove();
		}
		
		// If this isn't a temporary chat:
		if(params.id.toString().charAt(0) != 't'){
			var previous = $('#chatLineHolder .chat-'+(+params.id - 1));
			if(previous.length){
				previous.after(markup);
			}
			else chat.data.jspAPI.getContentPane().append(markup);
		}
		else chat.data.jspAPI.getContentPane().append(markup);
		
		// As we added new content, we need to reinitialise the jScrollPane plugin:
		chat.data.jspAPI.reinitialise();
		chat.data.jspAPI.scrollToBottom(true);
		
	},
	
	// This method requests the latest chats (since lastID), and adds them to the page.
	getChats : function(callback)
    {
		$.chatGET('getChats',{lastID: chat.data.lastID},function(r){
			
			for(var i = 0; i < r.chats.length; i++){
				chat.addChatLine(r.chats[i]);
			}
			
			if(r.chats.length){
				chat.data.noActivity = 0;
				chat.data.lastID = r.chats[i-1].id;
			}
			else{
				// If no chats were received, increment the noActivity counter.
				chat.data.noActivity++;
			}
			
			if(!chat.data.lastID){
				chat.data.jspAPI.getContentPane().html('<p class="noChats">No chats yet</p>');
			}
			
			// Setting a timeout for the next request, depending on the chat activity:
			var nextRequest = 1000;
			
			// 2 seconds
			if(chat.data.noActivity > 3){
				nextRequest = 2000;
			}
			
			if(chat.data.noActivity > 10){
				nextRequest = 5000;
			}
			
			// 15 seconds
			if(chat.data.noActivity > 20){
				nextRequest = 15000;
			}

            if(chat.currentUser.is_connected){
                setTimeout(callback,nextRequest);
            }
		});
	},

    registerUser : function(sUserName, sUserEmail)
    {
        var actionString = "name=" + sUserName + "&email=" + sUserEmail;
        $.chatPOST('register',actionString,function(r){
            if(r.error){
                chat.displayError(r.error);
            }
        });
    },

    loginUser : function(sUserName, sUserEmail)
    {
        var actionString = "name=" + sUserName + "&email=" + sUserEmail;
        $.chatPOST('login',actionString,function(r){
            if(r.error){
                chat.displayError(r.error);
            }
            else{
                $.chatGET('userIsAdmin',function(r){
                    if(r.result == 1){
                        chat.currentUser.is_admin = true;
                    }
                });

                chat.login(r.name,r.gravatar);
            }
        });
    },

    countUsers : function()
    {
	  $.chatGET('countUsers',function(r){
	      return r.total;
      });
    },

	getStatus : function(callback)
    {
        $.chatGET('getStatus',function(r){
            if(r.is_blocked){
                chat.logout();
                chat.displayError("You were blocked by the admin!");
            }

            if(chat.currentUser.is_connected){
                setTimeout(callback, 3000);
            }
        });
    },

    // Requesting a list with all the users.
	getUsers : function(callback)
    {
		$.chatGET('getUsers',function(r){
			var users = [];
            var message = '';

			for(var i = 0; i < r.users.length; i++){
				if(r.users[i]){
					users.push(chat.render('user',r.users[i]));
				}
			}
			
			if(r.total < 1){
				message = 'No one is online';
			} else {
				message = r.total+' '+(r.total == 1 ? 'person':'people') + ' online';
			}
			
			users.push('<p class="count ">' + message + '</p>');
			
			$('#chatUsers').html(users.join(''));

			if(callback !== null && chat.currentUser.is_connected){
                setTimeout(callback,15000);
            }
		});

		// If admin is logged in, load blocked users too
		if(chat.currentUser.is_admin){

            $.chatGET('getBlockedUsers',function(r){

                var users = [];

                for(var i = 0; i < r.users.length; i++){
                    if(r.users[i]){
                        users.push(chat.render('blockedUser',r.users[i]));
                    }
                }

                var message = '';

                if(r.total<1){
                    message = 'No one is blocked';
                } else {
                    message = r.total + ' ' + (r.total == 1 ? 'person is':'people are') + ' blocked';
                }

                users.push('<p class="count">' + message + '</p>');

                $('#blockedUsers').html(users.join(''));
            });
        }
	},

    // Admin function
    blockUser : function(newUserName)
    {
        if(newUserName !== null && newUserName !== ''){
            $.chatGET('blockUser',{userName: newUserName},function(r) {
                if(r.result) {
                    chat.getUsers(null);  // Refresh user list without blocked user
                } else {
                    chat.displayError("Couldn't block user \"" + newUserName + "\"!");
                }
            });
        }
    },

    unBlockUser : function(newUserName)
    {
        if(newUserName !== null && newUserName !== ''){
            $.chatGET('unBlockUser',{userName: newUserName},function(r) {
                if(r.result) {
                    chat.getUsers(null);  // Refresh user list without blocked user
                } else {
                    chat.displayError("Couldn't unblock user \"" + newUserName + "\"!");
                }
            });
        }
    },
	
	// This method displays an error message on the top of the page:
	displayError : function(msg)
    {
		var elem = $('<div>',{
			id		: 'chatErrorMessage',
			html	: msg
		});
		
		elem.click(function(){
			$(this).fadeOut(function(){
				$(this).remove();
			});
		});
		
		setTimeout(function(){
			elem.click();
		},5000);
		
		elem.hide().appendTo('body').slideDown();
	}
};

// Custom GET & POST wrappers:
$.chatPOST = function(action,data,callback)
{
	$.post('php/ajax.php?action='+action,data,callback,'json');
}

$.chatGET = function(action,data,callback)
{
	$.get('php/ajax.php?action='+action,data,callback,'json');
}

// A custom jQuery method for placeholder text:
$.fn.defaultText = function(value)
{
	
	var element = this.eq(0);
	element.data('defaultText',value);
	
	element.focus(function(){
		if(element.val() == value){
			element.val('').removeClass('defaultText');
		}
	}).blur(function(){
		if(element.val() == '' || element.val() == value){
			element.addClass('defaultText').val(value);
		}
	});
	
	return element.blur();
}
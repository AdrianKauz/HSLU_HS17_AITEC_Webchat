# HSLU HS17 AITEC - Webchat Task

Fix and extend a given (modified) webchat template, provided by HSLU.  
You can find the original source for this webchat here on[tutorialzine.com](https://tutorialzine.com/2010/10/ajax-web-chat-php-mysql)  
End time for this exercise: **2017.11.29**


## Getting Started
###Setup the webchat

1. Make sure you have installed the latest version of Apache, PHP and phpMyAdmin
2. Use the provided virtual machine "**AITEC v1.1 HS2016**", which you can find on the "HSLU ILIAS [Repository](https://elearning.hslu.ch/ilias)", if you won't to setup your own webserver. 
3. Copy all the files into your webfolder
4. Run SQL-Script ["db_prepare_database.sql"](sql/prepare_database.sql)
5. Test conncetion with ["db_connection_test.php"](php/db_connection_test.php)
6. Now it should work. If not, your problem :baby_chick: 

###Tasklist
_Source: "02b.PHP_Projekt_v4.pdf"_
- [x] Setup the template and let it run on your webserver _(2017.11.14)_
- [ ] Add Self-Registration
- [ ] Add an admin role (to manage the chat and the users)
- [ ] Admin can activate new users
- [ ] Admin can block an user
- [ ] Admin can remove an user
- [ ] Install a stable input validation for chat-messages (For security and stuff)

Free for fun:
- [ ] Remove dependencies from online-libraries. As an example: Loading jQuery-Files directly from Google.
- [ ] Explore the magic about Gravatars and why they exists.

Own stuff:
- [ ] Cleaning code
- [ ] Some "eye candy" stuff
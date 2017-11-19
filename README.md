# HSLU HS17 AITEC - Webchat Task

Fix and extend a given (modified) webchat template, provided by HSLU.  
You can find the original source for this webchat here on ["tutorialzine.com"](https://tutorialzine.com/2010/10/ajax-web-chat-php-mysql)  
End time for this exercise: **2017.11.29**


## Getting Started
### Setup the webchat

1. Make sure you have installed the latest version of Apache, PHP and phpMyAdmin
2. Use the provided virtual machine "**AITEC v1.1 HS2016**", if you won't setup your own webserver. You can find the image on the ["HSLU ILIAS Repository"](https://elearning.hslu.ch/ilias). 
3. Copy all the files into your webfolder
4. Run SQL-Script ["db_prepare_database.sql"](setup/prepare_database.sql)
5. Test conncetion with ["db_connection_test.php"](php/db_connection_test.php)
6. Now it should work. If not, your problem :baby_chick: 

### Tasklist
_Source: "02b.PHP_Projekt_v4.pdf"_
- [x] Setup the template and let it run on your webserver _(2017.11.14)_
- [ ] Add Self-Registration
- [ ] Add an admin role (to manage the chat and the users)
- [ ] Admin can activate new users
- [ ] Admin can block an user
- [ ] Admin can remove an user
- [x] Install a stable input validation for chat-messages (For security and stuff) _(2017.11.19)_

Free for fun:
- [x] Remove dependencies from online-libraries. As an example: Remove loading jQuery-Files directly from Google. _(2017.11.18)_
- [x] Explore the magic about Gravatars and why they exists. _(2017.11.18)_

Own stuff:
- [ ] Cleaning code
- [ ] Some "eye candy" stuff

### Notes
- Extended the "text"-field from varchar(255) to varchar(2048). This should be enough to save 255 char-text lines with activated HTML-Entity-Encoding.<br>As an example: "·" takes 8 characters as HTML-Entity.
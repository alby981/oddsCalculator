# Odds Calculator

I have to make an important premise. I didn't have much time to do the test due to the actual current workload and weekend sickness. So following  some important notes:

* Technology used: I haven't followed 100% the requirements. For instance i didn't use any template engine or SASS table, but it won't be a problem if need it;
* Good practice: Some of the code needs to handle exceptions in the proper way eg. calls made to the API / wordpress;
* Style can be improved a lot, at the moment i consider it a functioning draft; 
* Some of the requirements were not 100% clear to me but i tried anyway to show all the complete flow;
* Templates: right now all the templates are hooked into the footer. I would create some widget instead and / or some shortcodes; 
* Fractal method: is not hard to implement but i don't have time to do it now. The most difficult was probably the American but nothing crazy. A technical note here. bcmath for what concerns this kind of operations is better than what i have done, but requires module installed etc... so i prefered to skip it (for now :))
* API KEY / config file: You need to replace the API KEY in the config file. The idea should be have this key stored in the DB or in the wp-config.php file but i guess is easier like this for testing purposes. 

For sure i forgot something, but i guess that what you are seeing is enough. Let me know if you need more info. 

# FakeBuk - A made from scratch social network

![EXAMPLE front page](projectation\index.png) 

## What it is?

This was a side project I made while I was studying web applications; 
In short it is a copy of Facebook made purely from scratch with basic web technologies like HTML, CSS, JS and in the backend PHP and MariaDB SQL.

## How does it works?

As you can imagine this IS NOT the way huge social media works (since they use different types of database technology and different types of distributed system) but it is still a great way to visualize an advanced web application that uses basic technologies.

So... how does it work exactly?

Since it doesn't have any type of data collection or data analysis with an algorithm it just loads random posts made from users in the front page.

Each user can register or log in creating a session (that is different from the PHP integrated one) which needs to be always valid from the Database, with a valid sessions a User can post, edit their profile and leave a feedback for the developer.

This website has roles which are:
- Guest -> whoever is not registered or logged in
- User -> normal user who is logged in
- Moderator -> well... he can delete posts or ban users
- Moderator Chief -> moderator on steroids
- Admin -> moderator but with more priviledges, he can also make announcements
- Admin Chief -> admin on steroids
- Founder -> Reserved for whoever is hosting the website.


## Installation

Installation is really easy.

1. Install Apache and MariaDB (if you're a beginner just install XAMPP)

2. Download this entire project

3. If you have installed XAMPP (or LAMPP for linux os) put the extracted folder into htdocs inside the XAMPP folder and then start Apache and MariaDB from the GUI.
If you downloaded Apache and MariaDB without XAMPP start MariaDB server with:
- ON WINDOWS
```bash
net start mysql
```
- ON LINUX
```bash
sudo systemctl start mariadb
```

- ON MAC OS:

On Mac you will need Homebrew, install it and then 
```bash
brew install mysql
brew services start mariadb
```

After being sure MariaDB is running you will need to start Apache:

Again, for beginners here I recommend using XAMPP, if you want to run it separately make sure you have installed Apache, then execute:

- On Windows:
```bash
httpd -k restart
```
If it doesn't work check the folder where apache is installed and run it again from there.

- On Linux/Mac:
```bash
sudo apachectl start
```

4. Now you need to populate the database server with the tables and the right setup, doing that is easy.
Make sure MariaDB and Apache are running.

IF you're using XAMPP:

- Go to your browser
- type URL " 127.0.0.1:80 "
- Click on top " Phpmyadmin "
- On top click " Import " and select "my_testwebsitefkb.sql" file that you will find on the project folder

if you're not using XAMPP:

 - Open your terminal
 - Type 
 ```bash
  mysql -u root -p
``` 
  and press enter two times

 - Once there you need to import the file, type
 ```sql
    CREATE DATABASE IF NOT EXISTS my_testwebsitefkb;
    USE my_testwebsitefkb;
 ```
 and right after
 ```sql
    source /path/to/your/file.sql;
 ```

 

5. You're practically done, now you just need to access on the website, open your favorite browser and type on the URL bar ' 127.0.0.1:80/[name_of_the_folder_the_project_is_stored] '.
If it doesn't show anything re-check if Apache is running or you inserted the right folder name.

## TROUBLESHOTING

- If the browser load errors that are like "Query couldn't be executed blah blah blah" that means you need to revisit step 4.

- If you don't see the top blue bar that means either some files are missing or you're using a wrong version of PHP, to fix that check the folder or reinstall Apache (or use XAMPP, again, higly reccomended for beginners).

## NOTE WELL:

The website comes with no data or anything, it is empty, you can edit it as how much you like.
I'm glad if this little project helped you in some way


## Advanced info

E/R scheme of the Database

![ER scheme of the database](projectation\schemaER_semplificato.png) 

Logic scheme of the Database

![Logic scheme of the database](projectation\schema_logico.png) 

RAPID Q&A:

- How does the website load other posts without reloading?

There is a trigger, when you reach 2/3 of the page loaded it sends a JavaScript trigger to a backend application that will return other posts that JavaScript will load on the moment.

- How does leaving a like or a comment works?

For likes it is a javascript trigger that sends the information on the server, information such as id of the user and id of the post (See the database scheme to see how it is stored).
Comments same thing

- How does a Ban work?

There are two types of ban, account ban and IP ban.
Account ban will take the account and put it in a "blacklist", it means if the user tries to log in from there it will simply block the entire website from them showing the ban reason.

IP bans are pretty much the same thing as the account ban but it adds another layer... It extends for the entire public IP address of the user.

- Was this Vibe Coded?

No. It was made entirely from scratch brick by brick with my keyboard.

## Cybersecurity and Bugs

This website was never intented for having actual users lol.

But since I made it for learning purposes I implemented basic cybersecurity prevention, there is no place a SQL injection can be executed and I made sure the backend of the server verified every single request before doing anything.

If you notice something that could be improved or fixed, email me with how did you found the problem and if you have a solution to send me that too, any help is appreciated!

## Editing the code

You can do whatever you want with the code, use it on your projects, edit it or host it as long as this helps someone I don't care.
You can find some documentation on projectation folder and in the comments of the files.

## License

This project is licensed under the MIT License

## Contact

This website was made by Biagio Valerio Carace - 
Contacts: biagiovalerio9@gmail.com

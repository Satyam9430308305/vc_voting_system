# vc_voting_system
 This is a minor ptoject on visual cryptograpgy aidded voting system.
# Description of project 
 project folder consist of all the required files for execution of voting system
 ## steps for setup
 1. download xampp
 2. make database in MySql
       - database name - voting_system
       - tabel_1 = login (voter_id - primary varchar(255)
       -                  username - varchar(255)
       -                  email - varchar(255)
       -                  password - varchar(255) )
       - table_2 = shares( share_id - primary int AI
       -                   voter_id - varchar(255)
       -                   share_1 - varchar(255)
       -                   share_2 - varchar(255)
       -                   captcha - varchar(255)
       -                   shift - int)
 3. run the xampp contro pannel
 4. run appcahe server and my sql server
 5. open the htdocs folder and delete the index.php file
 6. paset the project folder within it
 7. go to localhost and run navigate to project folder

core3
=====
PHP framework for business application.

NOTE: Currently it's russian framework. So you'll get no way to translate any inner locutions. In the future the translator will be available as a system module.

Minimum Server Requirements
---------------------------

* PHP 7.4 or greater
* PDO PHP extension
* MySQL or PostgreSQL Database


Installation
------------
1. Put the sources inside *core3* folder anywhere on your server.
2. Create MySQL schema with [db.sql](db.sql)
3. Create *admin* user with the same password.
```sql
  INSERT INTO `core_role` () VALUES ('');
  INSERT INTO `core_users` (`login`, `pass`, `visible`, `is_admin_sw`) VALUES ('admin', 'ad7123ebca969de21e49c12a7d69ce25', 'Y', 'Y');
```

4. Create *index.php* file anywhere inside the document root. Make sure that *core3* folder is available from its place.
```php
    try {
        require_once("core3/inc/classes/Error.php");
        require_once("core3/inc/classes/Init.php");
    
        $init = new Core3\Init();
        $init->checkAuth();
    
        echo $init->dispatch();
    } catch (Core3\Error $e) {
        Error::catchException($e);
    }
```
5. Create *conf.ini* file near the index.php
 
```ini
[production]
system.database.params.host      = localhost
system.database.params.port      = 3306
system.database.params.dbname    = <database name>
system.database.params.username  = <database user>
system.database.params.password  = <user password>
```

Usage
-----
Open URL of new index.php file in your browser. Use 'admin' username and 'admin' password.


Config parameters
-----------------
#### General
- **system.name**                 System name
- **system.host**                 Host name
- **system.logo**                 The path to image file
- **system.timezone**             Timezone name
- **system.debug.on**             Debug - true or false
- **system.debug.firephp**        Debug through FirePHP - true or false
- **system.https**                System use https - true or false
- **system.theme**                Theme name
- **system.cache**                The path to the cache folder
- **system.temp**                 The path to the temp folder
- **system.include_path**         Added your include path
- **system.php.path**             Path to php
- **system.disable.on**           Disable system - true or false
- **system.disable.title**        The reason for system disable
- **system.disable.description**  Details of the system disable

#### Database
- **system.database.params.host**            Host database server
- **system.database.params.port**            Port number to database server
- **system.database.params.dbname**          Database name on database server
- **system.database.params.username**        User name to auth
- **system.database.params.password**        Password to auth
- **system.database.params.params.charset**  Charset name to connect database - utf8, utf8mb4 or another

#### Mail
- **system.mail.server**   Host mail server
- **system.mail.port**     Port number to mail server
- **system.mail.auth**     Auth method name - plain, login or crammd5
- **system.mail.username** User name to auth
- **system.mail.password** Password to auth
- **system.mail.ssl**      Secure connection - ssl or tls 

#### Log
- **system.log.on**     Log active - true or false
- **system.log.writer** Method write log - file or database
- **system.log.file**   Log file path

#### Ldap
- **system.ldap.active**                  Ldap active - true or false
- **system.ldap.root**                    User login with root privileges
- **system.ldap.admin**                   User login with admin privileges
- **system.ldap.host**                    Host ldap server
- **system.ldap.port**                    Port number to ldap server
- **system.ldap.username**                User name to auth
- **system.ldap.password**                Password to auth
- **system.ldap.bindRequiresDn**          Account used to bind if the username is not already in DN form - true or false
- **system.ldap.baseDn**                  This option is required for most account related operations and should indicate the DN under which accounts are located
- **system.ldap.accountCanonicalForm**    A small integer indicating the form to which account names should be canonicalized. 
- **system.ldap.accountDomainName**       Domain name
- **system.ldap.accountDomainNameShort**  Domain sort name
- **system.ldap.accountFilterFormat**     The LDAP search filter used to search for accounts. 
- **system.ldap.allowEmptyPassword**      Allowed empty password - true or false
- **system.ldap.useStartTls**             Use start tls - true or false
- **system.ldap.useSsl**                  Use ssl - true or false
- **system.ldap.optReferrals**            LDAP client that referrals should be followed - true or false
- **system.ldap.tryUsernameSplit**        Username should not be split at the first @ or \ character - true or false

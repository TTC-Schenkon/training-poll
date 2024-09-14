# Training Poll
This repository provides a simple training poll for sports clubs to manage their training sessions. Please note that it requires a custom configuration to function properly.

## Configuration
Before using the poll, ensure you have a config.php file set up with the following variables. This file should be located two folders above the root of the repository.

```php
Copy code
$server = "YOUR SERVER";   // Database server address
$dbuser = "YOUR USER";     // Database username
$dbpwd  = "YOUR PASSWORD"; // Database password
$db     = "YOUR DATABASE"; // Database name
$conn   = NULL;            // Connection variable
$host = $_SERVER['REMOTE_ADDR']; // IP address of current request
$admin_ip = "YOUR IP ADDRESS";   // IP address for admin access
$max_limit = 500;               // Daily limit for poll submissions (NULL = no limit)
$today = date("d.m.Y", time());                          // Current date
$week = date('W', time());                               // Current week number
$days = array('SO', 'MO', 'DI', 'MI', 'DO', 'FR', 'SA'); // Days of the week
```
### Instructions
1. Set the database credentials ($server, $dbuser, $dbpwd, and $db) to match your environment.
2. Ensure the admin_ip is set to the IP address you want to allow admin access from.

## License
This project is licensed under the MIT License. See the [LICENSE](./license.txt) for details.
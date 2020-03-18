php version >= 7.0

usages:

php get-code.php <name> get code for key named as <name>

php add-code.php <name> <key> - add key to database with name <name>

php key.php - generates random key

default database ~/.gcode/keys

example:
git clone https://github.com/angmarb/google-code-cli && cd google-code-cli
php add-code.php test <code> 
php get-code.php test

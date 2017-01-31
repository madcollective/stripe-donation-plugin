# Curtesy of Cully Larson (@cullylarson)
cd /var/www/html

echo "Installing Wordpress..."
wp core install --url="localhost:8080" --title="Stripe Donation Form Dev" --admin_user=admin --admin_password=admin --admin_email=admin@localhost.localdomain

echo "Setting some options..."
wp option update blogdescription ""
wp option update permalink_structure "/%postname%/"
wp option update timezone_string "America/Los_Angeles"
wp option update start_of_week "0"

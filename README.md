# FuelPHP Template
This is a basic FuelPHP 1.8.2 project template configured to run without XAMPP, using PHP’s built-in server. It includes setup instructions for Linux (Azure) and local environments.

# Project Structure
fuelphp-project/
├── fuel/             # FuelPHP core framework
├── app/              # Your application code
├── public/           # Web root (entry point)
├── composer.json     # Composer dependencies
├── README.md         # Setup instructions

# Requirements
* PHP 5.6 to 7.4
*  Composer
*  Git
*  Internet access (for installing packages)

# Run Locally (No XAMPP)

1. Clone the Repository
bash
git clone https://github.com/your-username/your-repo.git
cd your-repo

2. Install Dependencies
bash
composer install

3. Set Permissions
bash
chmod -R 775 fuel/app/cache
chmod -R 775 fuel/app/logs

4. Configure Base URL
In fuel/app/config/config.php, set:
php
'base_url' => 'http://localhost:8000/',

5. Start PHP Built-in Server
bash
php -S localhost:8000 -t public

Open http://localhost:8000 in your browser.

# Setup on Linux (Azure VM)
Use the following steps to run this project on an Azure VM (Ubuntu).
1. Install Required Packages
bash
sudo apt update && sudo apt upgrade -y
sudo apt install php php-mbstring php-xml php-mysql php-cli unzip curl git -y

2. Install Composer
bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer -V

3. Clone the Repository
bash
git clone https://github.com/your-username/your-repo.git
cd your-repo

4. Install Dependencies
bash
composer install

5. Set File Permissions
bash
chmod -R 775 fuel/app/cache
chmod -R 775 fuel/app/logs

6. Set the Base URL
Edit fuel/app/config/config.php:
php
'base_url' => 'http://<your-vm-ip>:8000/',

7. Run the App
bash
php -S 0.0.0.0:8000 -t public

Visit the app in your browser:

http://<your-azure-vm-ip>:8000

8. Allow Port 8000 in Azure Portal
* Go to your VM -> Networking
* Add Inbound port rule:
Port: 8000
Protocol: TCP
Action: Allow

* Port: 8000
* Protocol: TCP
* Action: Allow

# Useful Commands
| Task                  | Command                          |
|-----------------------|----------------------------------|
| Run server            | php -S localhost:8000 -t public|
| Run oil CLI           | php oil                        |
| Migrate DB            | php oil refine migrate         |
| Clear cache           | php oil refine clear           |
# Troubleshooting
* 404 or FuelPHP not loading: Make sure you run the built-in server from the root:
bash
php -S localhost:8000 -t public

* Permission errors: Run: bash
chmod -R 775 fuel/app/cache fuel/app/logs

* Permission errors: Run: bash
chmod -R 775 fuel/app/cache fuel/app/logs

* PHP or Composer not found: Install them using steps above.
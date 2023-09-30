## Requirements
- Download and install composer (https://getcomposer.org/download/)
- Download and install NodeJS (https://nodejs.org/en/)
- Check NPM version ```npm -v```
- Check composer version ```composer -v```
- Check NodeJS version ```node -v```

## Setup and Installation
####1: Run the following command to install all the dependencies:
```bash
composer install
npm install
```

```bash
php -S localhost:8000
```



# General Config
ANALYTICS="null"

# App Config
JOURNAL_ID="72"
JOURNAL_TITLE="Saudi Journal of Emergency Medicine"
JOURNAL_ABBREV="sjemed"
JOURNAL_DOMAIN="https://sjemed.com/"
JOURNAL_ISSN=""
JOURNAL_EISSN="1658-8487"
JOURNAL_PUBLISHER="Waraqa Scientific Publishing House / Discover STM Publishing Ltd"

# Database Config
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_USERNAME=sjemed_admin
DB_DATABASE=sjemed_admin
DB_PASSWORD=0hMyG0d683Z!


# Debug Config
APP_DEBUG=false
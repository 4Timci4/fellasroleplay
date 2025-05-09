# Fellas Roleplay Website

This project is a website and management system developed for the Fellas Roleplay community. It includes features such as a forum, application system, Discord integration, and admin panel.

## Features

- **Forum System**: Fully functional forum with categories, topics, and comments
- **Application System**: Application form for joining the roleplay server
- **Discord Integration**: Integration with Discord server, role management
- **Admin Panel**: User management, application approval, forum moderation
- **Responsive Design**: Mobile-friendly interface

## Installation

1. Upload the files to your web server
2. Configure the database settings:
   - Create an `.env` file (you can copy the `.env.example` file)
   - Enter your database connection information
3. Configure the Discord API settings:
   - Enter your Discord Bot Token, Guild ID, and Role IDs in the `.env` file
   - Edit the `discord-bot/config.json` file
4. Start the Discord bot:
   - Navigate to the `discord-bot` directory
   - Run the `npm install` command
   - Start the bot with `node index.js` or `start-bot.bat`

## Database Structure

The project uses two databases:
- `fellasrpweb`: For the website (users, forum, applications)
- `fellasrp`: For the game server (characters, inventory, etc.)

## Discord Bot

The Discord bot provides the following functions:
- Automatic role assignment for approved applicants
- Sending direct messages to users
- Role checking and management

## Configuration Files

- `.env`: All sensitive information and configuration settings
- `includes/config/database.php`: Database connection information
- `includes/config/discord.php`: Discord API configuration
- `includes/config/config.php`: General site settings
- `discord-bot/config.json`: Discord bot configuration

## Development

The project is developed with PHP and uses the following structures:
- Core classes with OOP approach
- MVC-like architecture
- Discord API integration
- Node.js-based Discord bot

## Post-Installation

1. Create an admin account
2. Check Discord bot permissions
3. Edit forum categories
4. Customize the application form

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Node.js 14 or higher (for Discord bot)
- Web server (Apache, Nginx, etc.)

## License

This project is for private use and belongs to the Fellas Roleplay community.

## Contact

Discord: [https://discord.gg/fellasrp](https://discord.gg/fellasrp)

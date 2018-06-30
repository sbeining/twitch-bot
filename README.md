# Twitch Bot

This Twitch Bot is mainly used for the Fronds Community
(twitch.tv/whoamattberry twitch.tv/keldamist and twitch.tv/bretmwxyz)
But maybe some commands are useful to somebody else.

More commands quite possibly will be added in the future.

If you find any bugs or want a new command you can open an issue.


## Requirements

### **PHP** (http://php.net/manual/en/install.php)

The bot is written in PHP. That means a PHP Runtime has to be installed.
The minimum supported version is 7.0.

### **Composer** (https://getcomposer.org/doc/00-intro.md)

This project uses composer to install any additional php libraries.


## Installation

Download or clone this repository. Then use a command line to change into this
directory and use `composer install` to install any remaining dependencies.


## Running the bot

To run the bot you will need a twitch account and an OAuth Token with the
correct credential. If you don't know what that is, log into you Bot twitch
account and open https://twitchapps.com/tmi/ in the browser.
This will generate an OAuth Token for that twitch account.
The token will be valid for a long time, so this step only has to be done once.

*You should still read up what an OAuth Token is.*

After that you can run the job by using a command line, changing into the
project directory and executing:

`bin/console twitch-bot:run :oauth_token: :bot_username: :channel:`

Just replace `:oauth_token:` with the generated Token, `:bot_username:` with
the twitch username of the bot (also the owner of the OAuth Token) and
:channel: with the channel your bot should be active in.


## Available commands

Command | Example | Category | Description
---|---|---|---
`Hi @:bot_username:` | Hi @AI_Yekara | Conversation | Greets the user back (the :bot_username: has to be replaced with the username of the bot)
`!pokemon :pokemon_name:` | `!pokemon farfetchd` | Pokémon | Outputs a short description of the pokémon
`!pokemon :pokemon_name:` `:pokemon_forme:` | `!pokemon shayimn sky` | Pokémon | If a pokémon has more than one forme it can be specified this way
`!poketype :type:` | `!poketype fire` | Pokémon | Describes every interaction (resistances, weaknesses, immunities) with this type
`!poketype :type: vs` | `!poketype flying vs` | Pokémon | Describes offensive interactions with this type
`!poketype :type: against`| `!poketype flying against` | Pokémon | Alias
`!poketype vs :type:` | `!poketype vs ice` | Pokémon | Describes defensive interactions with this type
`!poketype against :type:` | `!poketype against ice` | Pokémon | Alias
`!poketype :type: vs :type:` | `!poketype steel vs fairy` | Pokémon | Describes how the two types interact with each other
`!poketype :type: against :type:` | `!poketype steel against fairy` | Pokémon | Alias
`!you_died` | | Death Counter | Increments the death counter by one
`!undo_death` | | Death Counter | Decrements the death counter by one
`!set_deaths :number:` | `!set_deaths 666`| Death Counter | Sets the death counter a specific number
`!deaths` | | Death Counter | Outputs the death counter
`!vanquish :name:` | `!vanquish The Last Giant` | Bosskill Tracker | Adds the :name: to the list of killed bosses
`!unvanquish :name:` | `!unvanquish The Last Giant` | Bosskill Tracker | Removes the :name: from the list of killed bosses
`!vanquished:` | | Bosskill Tracker | Outputs the list of killed bosses

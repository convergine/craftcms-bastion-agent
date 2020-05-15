# Bastion Agent for Craft CMS 3.x

This is a companion plugin for [Bastion.Cloud](https://bastion.cloud) - managed maintenance service for CraftCMS. Plugin will enable website health telemetry gathering for Bastion service.


## Requirements

This plugin requires Craft CMS 3.1.0 or later. 


## Installation

You can install this plugin with Composer or through CraftCMS plugin store.

#### With Composer

Open your terminal and run the following commands:

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require criticalgears/craftcms-bastion-agent
        
3. Then tell Craft to install the plugin
    
        ./craft install/plugin craftcms-bastion-agent
        
#### CraftCMS Plugin Store

Alternatively, go to CraftCMS plugin store in your CraftCMS site, type "Bastion" in search and install from there.
 
## Configuring Bastion Agent

1. Create account at [Bastion.Cloud](https://bastion.cloud) and sign up for maintenance service.

2. Obtain the Secret Key from your account dashboard.

3. Now navigate to **Settings → Plugins → Bastion Agent** in your CraftCMS website and enter your Secret Key.

That's it for the configuration. Bastion service will now be able to get core & plugins health telemetry for your site.

## Found a bug?

Check the issues or [open a new one](https://github.com/criticalgears/craftcms-bastion-agent/issues)


---
Brought to you by [Critical Gears](https://www.criticalgears.com)
# Lights Manager

A manager for Philips Hue lights.

### Installation

Clone and copy the `.env.example` file to `.env` and set the variables.

### Bridge IP Address

If you don't know the IP address of your bridge you can run the following command:

```
php vendor/bin/phue-bridge-finder
```

### Bridge User

If you don't have a API user set up you can create one by running the command:

```
php vendor/bin/phue-create-user
```

## Usage

Run `php lights` to list all the available commands.

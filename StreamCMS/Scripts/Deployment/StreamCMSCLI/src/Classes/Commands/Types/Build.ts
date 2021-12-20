import {BaseCommand} from "../BaseCommand";
const { exec } = require("child_process");

class Build extends BaseCommand
{
    public getName(): string
    {
        return "build";
    }

    public run(...args): void
    {
        let commandArgs = [...args];
        if (commandArgs[0] === 'database') {
            console.log('Building database!');
            Build.buildDatabase();
        }
    }

    public getDescription(): string {
        return "build part of the StreamCMS application.";
    }

    private static buildDatabase(): void
    {
        // Clear the dir for our entities
        exec('php /var/www/StreamCMS/Scripts/Deployment/Database/DatabaseBuilder.php');
    }
}

module.exports = new Build();
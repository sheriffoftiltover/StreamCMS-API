import {BaseCommand} from "../BaseCommand";

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
            this.buildDatabase();
        }
    }

    public getDescription(): string {
        return "Build part of the StreamCMS application.";
    }

    private buildDatabase(): void
    {
        // Clear the dir for our entities
        this.exec('php /var/www/StreamCMS/Scripts/Deployment/Database/DatabaseBuilder.php');
    }
}

module.exports = new Build();
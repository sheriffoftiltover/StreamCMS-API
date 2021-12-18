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

    public getNameArgs(): string {
        return "build <part>";
    }

    private static buildDatabase(): void
    {
        // Clear the dir for our entities
        exec('')
    }
}

module.exports = new Build();
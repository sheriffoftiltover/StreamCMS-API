import {BaseCommand} from "../BaseCommand";
const { exec } = require("child_process");

class Composer extends BaseCommand
{
    public getName(): string
    {
        return "composer";
    }

    public run(...args): void
    {
        let commandArgs = [...args];
        exec(
            `composer ${commandArgs.join(' ')}`,
            {
                cwd: "/var/www/StreamCMS"
            }
        );
    }

    public getDescription(): string {
        return "Run composer from the StreamCMS directory.";
    }
}

module.exports = new Composer();
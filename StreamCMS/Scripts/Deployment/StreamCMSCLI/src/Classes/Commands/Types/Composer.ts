import {BaseCommand} from "../BaseCommand";

class Composer extends BaseCommand
{
    public getName(): string
    {
        return "composer";
    }

    public run(...args): void
    {
        let commandArgs = [...args];
        this.exec(
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
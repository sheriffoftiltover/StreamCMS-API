import {BaseCommand} from "../BaseCommand";

class Seed extends BaseCommand
{
    public getName(): string
    {
        return "seed";
    }

    public run(...args): void
    {
        this.exec(
            `/var/www/StreamCMS/vendor/robmorgan/phinx/bin/phinx seed:run`,
            {
                cwd: "/var/www/StreamCMS/Scripts/Development/Phinx"
            }
        );
    }

    public getDescription(): string {
        return "Seed the database using Phinx.";
    }
}

module.exports = new Seed();
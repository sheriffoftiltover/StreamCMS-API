import {BaseCommand} from "../BaseCommand";

class Rebuild extends BaseCommand
{
    public getName(): string
    {
        return "rebuild";
    }

    public run(...args): void
    {
        this.exec(
            `StreamCMS composer build`,
        );
    }

    public getDescription(): string {
        return "Rebuild the CLI tool.";
    }
}

module.exports = new Rebuild();
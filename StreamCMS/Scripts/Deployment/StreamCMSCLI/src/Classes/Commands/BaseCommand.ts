import {exec} from "child_process";

export abstract class BaseCommand {
    public abstract getName(): string;

    public abstract getDescription(): string;

    public abstract run(...args): void;

    public exec(command: string, args: object = {}): void
    {
        exec(
            command,
            args
        ).stdout.pipe(process.stdout);
    }
}
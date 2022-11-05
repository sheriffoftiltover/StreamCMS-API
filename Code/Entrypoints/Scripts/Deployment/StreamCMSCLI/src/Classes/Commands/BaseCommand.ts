import {exec} from "child_process";
import {spawn} from "child_process";

export abstract class BaseCommand {
    public abstract getName(): string;

    public abstract getDescription(): string;

    public abstract run(...args): void;

    public exec(command: string, args: string[], options: object = {}): void
    {
        options['stdio'] = 'inherit';
        const process = spawn(
            command,
            args,
            options,
        );
        process.on('exit', (error) => {
            if (error) {
                console.log(error);
            }
        });
    }
}
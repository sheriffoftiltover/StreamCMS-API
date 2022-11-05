import {Command} from "commander";
import {BaseCommand} from "./Commands/BaseCommand";
import {getFilesRecursive} from "../Util/FileUtil";
const path = require("path");

export class StreamCMS
{
    private program: Command;
    private commands: any;

    constructor(program: Command)
    {
        // Set our program
        this.program = program;
        this.commands = {};
        // Now build our commands
        this.loadCommands();
    }

    public parse(args): void
    {
        // FIXME @sheriffoftiltover Update this to properly check/allow no args...
        if (args.length === 3) {
            args.push('');
        }
        this.program.parse(args);
    }

    public run(...args): void
    {
        let commandArgs = [...args];
        let commandName = commandArgs.shift();
        let command = this.getCommand(commandName);
        if (command) {
            command.run(...commandArgs);
        }
    }

    private loadCommands(): void
    {
        const commandDir = path.join(__dirname, 'Commands', 'Types');
        const commandFiles = getFilesRecursive(commandDir, '.js', 1);
        commandFiles.map((commandFile) => {
            this.loadCommand(require(commandFile));
        });
    }

    private loadCommand(command: BaseCommand): void
    {
        this.commands[command.getName()] = command;
        this.program.command(`${command.getName()} <...args>`)
            .description(command.getDescription())
            .action((...args) => {
                this.run(...[command.getName(), ...args]);
            });
    }

    private getCommand(commandName: string): BaseCommand
    {
        return this.commands[commandName] ?? null;
    }
}
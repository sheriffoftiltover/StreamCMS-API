export abstract class BaseCommand {
    public abstract getName(): string;

    public abstract getNameArgs(): string;

    public abstract getDescription(): string;

    public abstract run(...args): void;
}
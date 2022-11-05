import { Command } from 'commander';
import {StreamCMS} from "./Classes/StreamCMS";

const program = new Command();
program.version('0.0.1');

const streamCMSCLI = new StreamCMS(program);
streamCMSCLI.parse(process.argv);
// Idea: Stream plugin where when I type in a sensitive file, it shows fake content
// Then if a user using my website tries to use the sensitive content: EG: Logging into server with password or w/e, they get a naughty tag or w/e
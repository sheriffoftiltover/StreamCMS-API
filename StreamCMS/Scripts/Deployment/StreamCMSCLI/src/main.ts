import { Command } from 'commander';
import {StreamCMS} from "./Classes/StreamCMS";

const program = new Command();
program.version('0.0.1');

const streamCMSCLI = new StreamCMS(program);
streamCMSCLI.parse(process.argv);

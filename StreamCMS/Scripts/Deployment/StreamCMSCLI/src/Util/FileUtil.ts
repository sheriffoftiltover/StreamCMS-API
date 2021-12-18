import * as fs from "fs";
import * as path from "path";

/**
 * Recursively adds files to an array until maxDepth is reached, filtering by extension if one is provided
 * @param dirPath
 * @param extension
 * @param maxDepth
 * @param currentDepth
 */
export function getFilesRecursive(dirPath: string, extension: string|null = null, maxDepth: number|null = null, currentDepth: number|null = null): string[]
{
    // Only assign a current depth if the current depth is null, and max depth is not null.
    currentDepth ??= maxDepth && 1;
    // Flatten the array before returning
    return Array.prototype.concat(
        ...fs.readdirSync(dirPath)
        .map((filePath) => {
            const fullPath = path.join(dirPath, filePath);
            const isDir = fs.lstatSync(fullPath).isDirectory();
            const atMaxDepth = currentDepth > maxDepth;
            return isDir && !atMaxDepth ?
                getFilesRecursive(fullPath, extension, maxDepth, currentDepth ? currentDepth+1 : currentDepth) :
                fullPath;
        })
    )
    // Filter the array by the extension if it is set.
    // It's a bit inefficient since it loops regardless of whether or not extension is set but the reduce alternative isn't nice either
    .filter(filePath => filePath.endsWith(extension ?? ''));
}
import { resolve as resolveTs } from 'ts-node/esm';
import * as tsConfigPaths from 'tsconfig-paths';
import { pathToFileURL } from 'url';

// Load tsconfig.json
const { absoluteBaseUrl, paths } = tsConfigPaths.loadConfig();
let matcher;
if (absoluteBaseUrl && paths) {
  matcher = tsConfigPaths.createMatchPath(absoluteBaseUrl, paths);
}

export function resolve(specifier, context, nextResolve) {
  // Try to resolve using tsconfig paths
  if (matcher) {
    const match = matcher(specifier);
    if (match) {
      specifier = pathToFileURL(match).href;
    }
  }

  // Use ts-node's resolver
  return resolveTs(specifier, context, nextResolve);
}

// Re-export the other hooks from ts-node/esm
export { load, getFormat, transformSource } from 'ts-node/esm';

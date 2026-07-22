# Load Branch Context Skill

## Metadata
- **Name:** load-branch-context
- **Description:** Automatically checks the active Git branch and injects supplemental standalone Markdown context from `.claude-context/branches/<branch-name>.md` if it exists.
- **Trigger Phrases:** ["initialize session", "what branch am I on", "load context", "read my branch goals"]
- **Trigger Actions:** switching branches or on startup

## Instructions
When this skill is triggered or when a session initializes, execute the following workflow to safely extract and ingest branch-specific contexts:

1. Run the local git command to find the active branch name:
   `git branch --show-current`
   
2. Sanitize the output branch name into a filesystem-safe file name (e.g., replace `/` and `.` characters with hyphens if necessary to match your naming convention, or look for the exact literal branch name).

3. Look for a matching standalone markdown file inside the project directory:
   `.claude-context/branches/<branch-name>.md`

4. Execute one of the following conditional states based on file discovery:
   - **If the file exists:** Read its contents immediately using your file read utility. Append these specific rules, variables, and tasks into your active working context alongside the root `CLAUDE.md`.
   - **If the file does not exist:** Log a quiet internal acknowledgment that no branch-specific overlay was found, and proceed natively using only the fallback project rules.

5. Silent execution rule: Perform this look-up cleanly. Do not ask the user for permission to check the git branch or to read the context file unless explicit permission configurations intercept the workflow.

## Output Format
A brief, high-scannability status update indicating the current alignment state:
"⚡ **Branch Context Loaded:** Synchronized workspace context with `.claude-context/branches/<branch-name>.md`."


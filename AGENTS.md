# Project
The project is a multi-page landing site built for an educational school in Ukraine for children and teenagers for programming and English language training.  
The project does not include personal account functionality; it only supports application submission for enrollment. The site acts as a sales page so users ultimately submit an enrollment request.  
It is important to enable a high level of customization so all texts and images can be changed dynamically, and articles can be published to the Blog. This is accomplished with ACF Pro.  
One of the main technical capabilities is selecting a city via an interactive map or the navbar, after which regional news and regional promotions for that specific location are displayed.

### Rules:

1. Always read the `/docs` folder if you need additional project information.
2. Write the smallest possible number of lines to complete the task — fewer lines are better.
3. The entire site must be only in Ukrainian.
4. When you make a significant architectural change, add it to `/rules/structure.md`.
5. Describe business logic according to the single responsibility principle.
6. When planning implementation steps, break work into steps and add them to `/docs/plan.md`.
7. Use `/.agents/skills` to verify what you are going to do.
8. Always do exactly what was requested and respond with short status updates without long text.
9. For API work, use `/docs/api`.
10. For testing, use `/docs/testing.md`.
11. When adding a new feature not listed in `/docs/features.md`, add a feature description to `features.md` to keep our backlog.
12. For database work, use `/docs/database.md`.
13. Before starting work, always check the stack in `/docs/tech-stack.md`.
14. For security, always refer to `/docs/security.md`.
15. See `/docs/project.md` for more detailed project goals.
16. For architecture work, use `/rules/architecture.md` and always update `architecture.md` when a new architectural innovation appears.
17. Always follow the rules in `rules/rules.md`.
18. Update project launch instructions in `/README.md`.
19. For deployment preparation or deployment readiness checks, see `/docs/deployment.md`.
20. For guidance on using ACF Pro for content model configuration, see `/docs/content-model.md`.
21. Follow REST principles.
22. Always check `/docs/edge-cases` before starting.
23. For the latest documentation, always check `/docs/links.md`.
24. For CI/CD setup, refer to `/docs/deployment.md`.
25. All local WordPress development must use Docker and DDEV to ensure a consistent, reproducible environment across all developers and AI agents.

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

When the user types `/graphify`, use the installed graphify skill or instructions before doing anything else.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- Dirty graphify-out/ files are expected after hooks or incremental updates; dirty graph files are not a reason to skip graphify. Only skip graphify if the task is about stale or incorrect graph output, or the user explicitly says not to use it.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).

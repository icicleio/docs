site_name: 'Icicle documentation'
site_description: 'Write asynchronous code using synchronous coding techniques in PHP'
site_author: 'Aaron Piotrowski and contributors'
copyright: '&copy; Aaron Piotrowski and contributors.'
theme_dir: theme
strict: true

markdown_extensions:
    - admonition
    - def_list
    - toc:
        permalink: " "

pages:
- Introduction: 'index.md'
- About the Documentation: 'meta.md'
- Installing: 'installing.md'
- Manual:
    - Introduction to Asynchronous Programming: 'manual/introduction.md'
    - The Event Loop: 'manual/loop.md'
    - Awaitables: 'manual/awaitables.md'
    - Coroutines: 'manual/coroutines.md'
    - Console Input and Output: 'manual/console.md'
    - Making DNS Queries: 'manual/dns-queries.md'
    - Concurrency: 'manual/concurrency.md'
    - Foreign Asynchronous Code: 'manual/foreign-async-code.md'
- API: %API_DOCS

extra:
    version: v1.x

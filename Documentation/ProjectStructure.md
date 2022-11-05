# Framework
This contains code that is completely self-contained and feature=agnostic.
It does not actually _do_ anything.
It just supports basic functionality in the Feature Framework

# Feature Framework
This contains code that is feature-dependent. It uses code in the Framework namespace and acts as
a framework for features. If a feature requires framework-like functionality, it will reside here.
Code here will still be relatively feature-agnostic, but it will not be project agnostic.

This means for example, in the StreamCMS project, that it will contain code related to handling
the requirement for API requests to have site contexts.

# Features
This is where the bulk of the code which actually performs actions lives.
Code in this namespace should only depend on code in the Feature Framework.
Code in this namespace should not depend on code in other features, or in the Framework namespace directly.



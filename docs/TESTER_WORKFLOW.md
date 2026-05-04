# Tester & Developer Workflow: Postman Integration

This document outlines the workflow for utilizing the architectural blueprints and pre-configured Postman collections.

## 1. Using the Postman Collection

The official API contract is maintained in the `postman/specs/index.yaml` file. The Lead Software Engineer uses this contract to automatically generate and maintain the collection hierarchy and technical documentation.

To begin development or testing:

1. Open the Postman Workspace linked to this repository.
2. Locate the **E-Commerce API** collection in the Collections sidebar.
3. All requests are pre-configured with the correct URLs, headers, and validation rules as defined in the architectural blueprints.

## 2. Setting up the Mock Server

To facilitate early testing before the backend implementation is finalized:

1. Click the `...` (more actions) next to the generated collection.
2. Select **Mock collection**.
3. Name the mock server (e.g., `E-Commerce Mock`).
4. Copy the generated **Mock URL**.
5. Update the `baseUrl` variable in your Postman environment with this URL.

## 3. Automated Testing (QA)

QA engineers perform their tests (Postman or Pest) strictly against the structure defined in the repository's collections. All endpoints return standardized `SuccessResponse` or `ErrorResponse` objects, ensuring zero deviation from the established contract.

## 4. Environment Variables

The project utilizes local environment templates configured with these standard variables:

- `baseUrl`: The target endpoint for the Laravel server or the Postman Mock URL.
- `adminEmail` / `adminPassword`: Pre-defined credentials for verifying authentication and role-based access logic.

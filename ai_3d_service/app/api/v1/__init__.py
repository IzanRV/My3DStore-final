"""
API v1 Router
"""

from fastapi import APIRouter
from app.api.v1.endpoints import generation, jobs, files, description

router = APIRouter()

router.include_router(generation.router, prefix="/generate", tags=["generation"])
router.include_router(jobs.router, prefix="/jobs", tags=["jobs"])
router.include_router(files.router, prefix="/files", tags=["files"])
router.include_router(description.router, prefix="/description", tags=["description"])
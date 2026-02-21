"""
Modelo de job para generación 3D
"""

from enum import Enum
from typing import Optional, Dict, Any
from datetime import datetime


class JobStatus(str, Enum):
    PENDING = "pending"
    PROCESSING = "processing"
    COMPLETED = "completed"
    FAILED = "failed"


class Job:
    def __init__(
        self,
        job_id: str,
        job_type: str,
        status: JobStatus = JobStatus.PENDING,
        input_data: Optional[Dict[str, Any]] = None,
        output_format: Optional[str] = None,
        output_file: Optional[str] = None,
        error: Optional[str] = None,
        created_at: Optional[str] = None,
        updated_at: Optional[str] = None,
    ):
        self.job_id = job_id
        self.job_type = job_type
        self.status = status
        self.input_data = input_data or {}
        self.output_format = output_format
        self.output_file = output_file
        self.error = error
        now = datetime.now().isoformat()
        self.created_at = created_at or now
        self.updated_at = updated_at or now

    def to_dict(self) -> Dict[str, Any]:
        return {
            "job_id": self.job_id,
            "job_type": self.job_type,
            "status": self.status.value,
            "input_data": self.input_data,
            "output_format": self.output_format,
            "output_file": self.output_file,
            "error": self.error,
            "created_at": self.created_at,
            "updated_at": self.updated_at,
        }

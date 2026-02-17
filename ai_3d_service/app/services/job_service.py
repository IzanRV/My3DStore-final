"""
Servicio para gestión de jobs
"""

import json
import os
from typing import Optional, List, Dict, Any
from datetime import datetime

from app.core.config import settings
from app.models.job import Job, JobStatus

class JobService:
    def __init__(self):
        self.jobs_dir = os.path.join(settings.CACHE_DIR, "jobs")
        os.makedirs(self.jobs_dir, exist_ok=True)
    
    def create_job(
        self,
        job_id: str,
        job_type: str,
        input_data: Dict[str, Any],
        output_format: Optional[str] = None
    ) -> Job:
        """Crea un nuevo job"""
        job = Job(
            job_id=job_id,
            job_type=job_type,
            status=JobStatus.PENDING,
            input_data=input_data,
            output_format=output_format
        )
        self._save_job(job)
        return job
    
    def get_job(self, job_id: str) -> Optional[Dict[str, Any]]:
        """Obtiene un job por ID"""
        job_file = os.path.join(self.jobs_dir, f"{job_id}.json")
        if not os.path.exists(job_file):
            return None
        
        with open(job_file, 'r') as f:
            return json.load(f)
    
    def update_job(
        self,
        job_id: str,
        status: Optional[JobStatus] = None,
        output_file: Optional[str] = None,
        error: Optional[str] = None
    ) -> bool:
        """Actualiza un job"""
        job_data = self.get_job(job_id)
        if not job_data:
            return False
        
        if status:
            job_data["status"] = status.value
        if output_file:
            job_data["output_file"] = output_file
        if error:
            job_data["error"] = error
        
        job_data["updated_at"] = datetime.now().isoformat()
        
        job_file = os.path.join(self.jobs_dir, f"{job_id}.json")
        with open(job_file, 'w') as f:
            json.dump(job_data, f, indent=2)
        
        return True
    
    def delete_job(self, job_id: str) -> bool:
        """Elimina un job"""
        job_file = os.path.join(self.jobs_dir, f"{job_id}.json")
        if os.path.exists(job_file):
            os.remove(job_file)
            return True
        return False
    
    def list_jobs(self, limit: int = 20, offset: int = 0) -> List[Dict[str, Any]]:
        """Lista jobs"""
        jobs = []
        if not os.path.exists(self.jobs_dir):
            return jobs
        
        job_files = sorted(
            [f for f in os.listdir(self.jobs_dir) if f.endswith('.json')],
            key=lambda x: os.path.getmtime(os.path.join(self.jobs_dir, x)),
            reverse=True
        )
        
        for job_file in job_files[offset:offset+limit]:
            job_path = os.path.join(self.jobs_dir, job_file)
            with open(job_path, 'r') as f:
                jobs.append(json.load(f))
        
        return jobs
    
    def _save_job(self, job: Job):
        """Guarda un job en disco"""
        job_file = os.path.join(self.jobs_dir, f"{job.job_id}.json")
        with open(job_file, 'w') as f:
            json.dump(job.to_dict(), f, indent=2)

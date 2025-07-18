import { TestBed } from '@angular/core/testing';

import { VersionCheckService } from './version-check.service';

describe('VersionCheckService', () => {
  let service: VersionCheckService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(VersionCheckService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
